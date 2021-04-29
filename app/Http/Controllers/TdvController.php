<?php

namespace HDSSolutions\Finpar\Http\Controllers;

use App\Http\Controllers\Controller;
use HDSSolutions\Finpar\DataTables\OrderDataTable as DataTable;
use HDSSolutions\Finpar\Http\Request;
use HDSSolutions\Finpar\Models\Branch;
use HDSSolutions\Finpar\Models\OrderLine;
use HDSSolutions\Finpar\Models\Payment;
use HDSSolutions\Finpar\Models\Variant;
use HDSSolutions\Finpar\Traits\CanProcessDocument;
use HDSSolutions\Finpar\Models\Currency;
use HDSSolutions\Finpar\Models\Customer;
use HDSSolutions\Finpar\Models\Order as Resource;
use HDSSolutions\Finpar\Models\Product;
use Illuminate\Support\Facades\DB;

class TdvController extends Controller
{
    use CanProcessDocument;

    protected function documentClass(): string
    {
        // return class
        return Resource::class;
    }

    protected function redirectTo(): string
    {
        // go to resource view
        return 'backend.tdv.show';
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        // load cash_books
        $customers = Customer::with([
            'identity',
            // 'addresses',
        ])->get();
        //
        $products = Product::with([
            'prices',
            'variants.prices',
        ])->get();
        //
        $branches = Branch::with([ 'warehouses.locators' ])->get();
        //
        $currencies = Currency::all();

        $payments = Payment::all();

        // show create form
        return view('sales::tdv.create', compact('customers', 'products', 'branches', 'currencies', 'payments'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // start a transaction
        DB::beginTransaction();

        // create resource
        $resource = Resource::make($request->input());
        $resource->transaction_date = now();
        $resource->partnertable()->associate(Customer::find($request->get('customer_id')));
        // save resource
        if (!$resource->save())
            // redirect with errors
            return back()
                ->withErrors($resource->errors())
                ->withInput();


        // check for errors
        if (count($resource->errors()) > 0)
            // redirect with errors
            return back()
                ->withInput()
                ->withErrors($resource->errors());

        // sync inventory lines
        if (($redirect = $this->syncLines($resource, $request->get('lines'))) !== true)
            // return redirection
            return $redirect;

        DB::commit();
        // check return type
        return $request->has('only-form') ?
            // redirect to popup callback
            view('sales::components.popup-callback', compact('resource')) :
            // redirect to resources list
            redirect()->route('backend.orders');
    }


    public function price(Request $request)
    {
        // get resources
        $product = $request->has('product') ? Product::findOrFail($request->product) : null;
        $variant = $request->has('variant') ? Variant::findOrFail($request->variant) : null;
        $currency = $request->has('currency') ? Currency::findOrFail($request->currency) : null;
        // return stock for requested product
        return response()->json($variant?->price($currency)?->pivot ?? $product?->price($currency)?->pivot);
    }

    private function syncLines(Resource $resource, array $lines)
    {
        // load inventory lines
        $resource->load(['lines']);

        // foreach new/updated lines
        foreach (($lines = array_group($lines)) as $line) {
            // ignore line if product wasn't specified
            if (!isset($line['product_id']) || is_null($line['quantity'])) continue;
            // load product
            $product = Product::find($line['product_id']);
            // load variant, if was specified
            $variant = isset($line['variant_id']) ? $product->variants->firstWhere('id', $line['variant_id']) : null;

            // find existing line
            $orderLine = $resource->lines->first(function ($iLine) use ($product, $variant) {
                    return $iLine->product_id == $product->id &&
                        $iLine->variant_id == ($variant->id ?? null);
                    // create a new line
                }) ?? OrderLine::make([
                    'order_id' => $resource->id,
                    'product_id' => $product->id,
                    'variant_id' => $variant->id ?? null,
                    'currency_id' => $resource->currency_id
                ]);

            // update line values
            $orderLine->fill([
                'price' => $line['price'] ?? 0,
                'quantity' => $line['quantity'] ?? null,
                'total' => $line['total'] ?? null,
            ]);
            // save inventory line
            if (!$orderLine->save()) {
                return back()
                    ->withInput()
                    ->withErrors($orderLine->errors());
            }


        }

        // find removed inventory lines
        foreach ($resource->lines as $line) {
            // deleted flag
            $deleted = true;
            // check against $request->lines
            foreach ($lines as $rLine) {
                // ignore empty lines
                if (!isset($rLine['product_id'])) continue;
                // check if line exists
                if ($line->product_id == $rLine['product_id'] &&
                    $line->variant_id == ($rLine['variant_id'] ?? null))
                    // change flag to keep line
                    $deleted = false;
            }
            // remove line if was deleted
            if ($deleted) $line->delete();
        }

        // return success
        return true;
    }

}
