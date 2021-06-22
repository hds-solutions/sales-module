<?php

namespace HDSSolutions\Finpar\Http\Controllers;

use App\Http\Controllers\Controller;
use HDSSolutions\Finpar\DataTables\InvoiceDataTable as DataTable;
use HDSSolutions\Finpar\Http\Request;
use HDSSolutions\Finpar\Models\Invoice as Resource;
use HDSSolutions\Finpar\Models\Currency;
use HDSSolutions\Finpar\Models\Customer;
use HDSSolutions\Finpar\Models\Employee;
use HDSSolutions\Finpar\Models\InvoiceLine;
use HDSSolutions\Finpar\Models\Order;
use HDSSolutions\Finpar\Models\Product;
use HDSSolutions\Finpar\Models\Variant;
use HDSSolutions\Finpar\Traits\CanProcessDocument;
use Illuminate\Support\Facades\DB;

class InvoiceController extends Controller {
    use CanProcessDocument;

    public function __construct() {
        // check resource Policy
        $this->authorizeResource(Resource::class, 'resource');
    }

    protected function documentClass():string {
        // return class
        return Resource::class;
    }

    protected function redirectTo():string {
        // go to resource view
        return 'backend.invoices.show';
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, DataTable $dataTable) {
        // check only-form flag
        if ($request->has('only-form'))
            // redirect to popup callback
            return view('sales::components.popup-callback', [ 'resource' => new Resource ]);

        // load resources
        if ($request->ajax()) return $dataTable->ajax();

        // return view with dataTable
        return $dataTable->render('sales::invoices.index', [ 'count' => Resource::count() ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request) {
        // load cash_books
        $customers = Customer::with([
            // 'addresses', // TODO: Customer.addresses
        ])->get();
        // load current company branches with warehouses
        $branches = backend()->company()->branches()->with([
            'warehouses',
        ])->get();
        // load employees
        $employees = Employee::all();
        // load products
        $products = Product::with([
            'images',
            'variants',
        ])->get()->transform(fn($product) =>
            // set Variant.product relation manually to avoid more queries
            $product->setRelation('variants', $product->variants->transform(fn($variant) =>
                $variant->setRelation('product', $product)
            ))
        );

        // get completed orders
        $orders = Order::with([
            'partnerable' => fn($partnerable) => $partnerable->with([ 'identity' ]),
        ])->completed()->invoiced(false)->get();

        $highs = [
            'stamping'          => $stamping = Resource::max('stamping') ?? null,
            'document_number'   => str_increment(Resource::where('stamping', $stamping)->max('document_number') ?? null),
        ];

        // show create form
        return view('sales::invoices.create', compact(
            'customers',
            'branches',
            'employees',
            'products',
            'orders',
            'highs',
        ));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request) {
        // cast values to boolean
        if ($request->has('is_purchase'))   $request->merge([ 'is_purchase' => $request->is_purchase == 'true' ]);
        if ($request->has('is_credit'))     $request->merge([ 'is_credit' => $request->is_credit == 'true' ]);

        // start a transaction
        DB::beginTransaction();

        // create resource
        $resource = new Resource( $request->input() );

        // associate Partner
        $resource->partnerable()->associate( Customer::findOrFail($request->partnerable_id) );

        // save resource
        if (!$resource->save())
            // redirect with errors
            return back()
                ->withErrors( $resource->errors() )
                ->withInput();

        // sync lines
        if (($redirect = $this->syncLines($resource, $request->get('lines'))) !== true)
            // return redirection
            return $redirect;

        // process order import
        if ($request->has('import') && ($redirect = $this->importOrders($resource, $request->input('orders'))) !== true)
            // return redirection
            return $redirect;

        // confirm transaction
        DB::commit();

        // check return type
        return $request->has('only-form') ?
            // redirect to popup callback
            view('sales::components.popup-callback', compact('resource')) :
            // redirect to resource details
            redirect()->route('backend.invoices.show', $resource);
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\Resource $resource
     * @return \Illuminate\Http\Response
     */
    public function show(Resource $resource) {
        // load inventory data
        $resource->load([
            'branch',
            'partnerable' => fn($partnerable) => $partnerable->with([ 'identity' ]),
            'employee' => fn($employee) => $employee->with([ 'identity' ]),
            'lines' => fn($line) => $line->with([
                'product.images',
                'variant' => fn($variant) => $variant
                    ->with([
                        'images',
                        'values',
                    ]),
                'orderLines' => fn($orderLine) => $orderLine
                    ->with([
                        'order',
                    ]),
            ]),
        ]);

        // redirect to list
        return view('sales::invoices.show', compact('resource'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\Resource $resource
     * @return \Illuminate\Http\Response
     */
    public function edit(Resource $resource) {
        // check if document is already approved or processed
        if ($resource->isApproved() || $resource->isProcessed())
            // redirect to show route
            return redirect()->route('backend.invoices.show', $resource);

        // load resource relations
        $resource->load([
            'lines' => fn($line) => $line->with([
                'product',
                'orderLines' => fn($orderLine) => $orderLine
                    ->with([
                        'order',
                    ]),
            ]),
        ]);

        // load customers
        $customers = Customer::all();
        // load current company branches with warehouses
        $branches = backend()->company()->branches()->with([
            'warehouses',
        ])->get();
        // load employees
        $employees = Employee::all();
        // load products
        $products = Product::with([
            'images',
            'variants',
        ])->get()->transform(fn($product) =>
            // set Variant.product relation manually to avoid more queries
            $product->setRelation('variants', $product->variants->transform(fn($variant) =>
                $variant->setRelation('product', $product)
            ))
        );

        // get completed orders
        $orders = Order::with([
            'partnerable' => fn($partnerable) => $partnerable->with([ 'identity' ]),
        ])->completed()->invoiced(false)->get();

        // show edit form
        return view('sales::invoices.edit', compact('customers', 'branches', 'employees', 'products', 'orders', 'resource'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Resource $resource
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id) {
        // find resource
        $resource = Resource::findOrFail($id);

        // cast values to boolean
        if ($request->has('is_purchase'))   $request->merge([ 'is_purchase' => $request->is_purchase == 'true' ]);
        if ($request->has('is_credit'))     $request->merge([ 'is_credit' => $request->is_credit == 'true' ]);

        // start a transaction
        DB::beginTransaction();

        // associate Partner
        $resource->partnerable()->associate( Customer::findOrFail($request->get('partnerable_id')) );

        // save resource
        if (!$resource->update( $request->input() ))
            // redirect with errors
            return back()
                ->withErrors( $resource->errors() )
                ->withInput();

        // sync lines
        if (($redirect = $this->syncLines($resource, $request->get('lines'))) !== true)
            // return redirection
            return $redirect;

        // process order import
        if ($request->has('import') && ($redirect = $this->importOrders($resource, $request->input('orders'))) !== true)
            // return redirection
            return $redirect;

        // confirm transaction
        DB::commit();

        // redirect to resource details
        return redirect()->route('backend.invoices.show', $resource);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\Resource $resource
     * @return \Illuminate\Http\Response
     */
    public function destroy($id) {
        // find resource
        $resource = Resource::findOrFail($id);
        // delete resource
        if (!$resource->delete())
            // redirect with errors
            return back()
                ->withErrors($resource->errors()->any() ? $resource->errors() : [ $resource->getDocumentError() ]);
        // redirect to list
        return redirect()->route('backend.invoices');
    }

    public function price(Request $request) {
        // get resources
        $product = $request->has('product') ? Product::findOrFail($request->product) : null;
        $variant = $request->has('variant') ? Variant::findOrFail($request->variant) : null;
        $currency = $request->has('currency') ? Currency::findOrFail($request->currency) : null;
        // return stock for requested product
        return response()->json($variant?->price($currency)?->pivot ?? $product?->price($currency)?->pivot);
    }

    private function syncLines(Resource $resource, array $lines) {
        // load inventory lines
        $resource->load(['lines']);

        // foreach new/updated lines
        foreach (($lines = array_group( $lines )) as $line) {
            // ignore line if product wasn't specified
            if (!isset($line['product_id']) || is_null($line['price']) || is_null($line['quantity'])) continue;
            // load product
            $product = Product::find($line['product_id']);
            // load variant, if was specified
            $variant = isset($line['variant_id']) ? $product->variants->firstWhere('id', $line['variant_id']) : null;

            // find existing line
            $orderLine = $resource->lines->first(function($iLine) use ($product, $variant) {
                return $iLine->product_id == $product->id &&
                    $iLine->variant_id == ($variant->id ?? null);
            // create a new line
            }) ?? InvoiceLine::make([
                'invoice_id'    => $resource->id,
                'currency_id'   => $resource->currency_id,
                'product_id'    => $product->id,
                'variant_id'    => $variant->id ?? null,
            ]);

            // update line values
            $orderLine->fill([
                'price_reference'   => $variant?->price( $orderLine->currency )?->pivot->price ?? $product->price( $orderLine->currency )?->pivot->price ?? $line['price'],
                'price_invoiced'    => $line['price'],
                'quantity_invoiced' => $line['quantity'],
                // 'total'             => $line['total'],
            ]);
            // save inventory line
            if (!$orderLine->save())
                return back()
                    ->withInput()
                    ->withErrors( $orderLine->errors() );
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

    private function importOrders(Resource $resource, array $orders) {
        // foreach orders
        foreach ($orders as $order) {
            // ignore if order was specified
            if ($order === null) continue;

            // load order
            $order = $order instanceof Order ? $order : Order::findOrFail($order);

            // foreach order lines
            foreach ($order->lines as $orderLine) {
                // ignore line if al ready invoiced
                if ($orderLine->is_invoiced) continue;

                // create InvoiceLine for current OrderLine
                $invoiceLine = $resource->lines()->withTrashed()
                    ->where('product_id', $orderLine->product_id)
                    ->where('variant_id', $orderLine->variant_id)
                    ->firstOr(fn() => new InvoiceLine($orderLine));

                // associate to current resource
                $invoiceLine->invoice()->associate($resource);
                $invoiceLine->fill([
                    'price_ordered'     => $orderLine->price_ordered,
                    'price_invoiced'    => $orderLine->price_ordered,
                    'quantity_ordered'  => $orderLine->quantity_ordered
                        // sum already existing orderLines
                        + $invoiceLine->orderLines->sum('pivot.quantity_ordered'),
                    'quantity_invoiced' => $orderLine->quantity_ordered - $orderLine->quantity_invoiced
                        // sum already existing orderLines
                        + $invoiceLine->orderLines->sum(fn($orderLine) => $orderLine->quantity_ordered - $orderLine->quantity_invoiced),
                ]);

                // save invoiceLine
                if (!$invoiceLine->save())
                    // redirect with errors
                    return back()->withInput()
                        ->withErrors( $invoiceLine->errors() );

                // link OrderLine with InvoiceLine
                $invoiceLine->orderLines()->attach($orderLine, [
                    'invoice_line_id'   => $invoiceLine->id,
                    'quantity_ordered'  => $orderLine->quantity_ordered - $orderLine->quantity_invoiced,
                ]);

                // untrash if was trashed
                if ($invoiceLine->trashed()) $invoiceLine->restore();
            }
        }

        // return success
        return true;
    }

}
