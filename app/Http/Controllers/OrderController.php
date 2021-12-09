<?php

namespace HDSSolutions\Laravel\Http\Controllers;

use App\Http\Controllers\Controller;
use HDSSolutions\Laravel\DataTables\Base\DataTableContract;
use HDSSolutions\Laravel\Http\Request;
use HDSSolutions\Laravel\Models\Order as Resource;
use HDSSolutions\Laravel\Models\Currency;
use HDSSolutions\Laravel\Models\Employee;
use HDSSolutions\Laravel\Models\OrderLine;
use HDSSolutions\Laravel\Models\Product;
use HDSSolutions\Laravel\Models\Variant;
use HDSSolutions\Laravel\Traits\CanProcessDocument;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

abstract class OrderController extends Controller {
    use CanProcessDocument;

    public function __construct() {
        // check resource Policy
        $this->authorizeResource(Resource::class, 'resource');
    }

    protected final function documentClass():string {
        // return class
        return Resource::class;
    }

    protected final function redirectTo():string {
        // go to resource view
        return 'backend.'.$this->prefix().'.orders.show';
    }

    protected abstract function documentType():string;

    protected final function isPurchaseDocument():bool { return $this->documentType() === 'purchase'; }
    protected final function isSaleDocument():bool { return $this->documentType() === 'sale'; }
    protected final function prefix():string { return Str::plural($this->documentType()); }

    // public abstract function index(Request $request, DataTableContract $dataTable);

    protected abstract function getPartnerable($partnerable);

    public final function store(Request $request) {
        // cast values to boolean
        $request->merge([ 'is_purchase' => $this->isPurchaseDocument() ]);

        // start a transaction
        DB::beginTransaction();

        // create resource
        $resource = new Resource( $request->input() );

        // set Customer through associate() to set partnerable_type
        $resource->partnerable()->associate( $this->getPartnerable($request->partnerable_id) );

        // save resource
        if (!$resource->save())
            // redirect with errors
            return back()->withInput()
                ->withErrors( $resource->errors() );

        // sync inventory lines
        if (($redirect = $this->syncLines($resource, $request->get('lines'))) !== true)
            // return redirection
            return $redirect;

        // confirm transaction
        DB::commit();

        // check return type
        return $request->has('only-form') ?
            // redirect to popup callback
            view('backend::components.popup-callback', compact('resource')) :
            // redirect to resource details
            redirect()->route('backend.'.$this->prefix().'.orders.show', $resource);
    }

    public final function show(Resource $resource) {
        // load inventory data
        $resource->load([
            'branch',
            'warehouse',
            'partnerable' => fn($partnerable) => $partnerable->with([ 'identity' ]),
            'employee' => fn($employee) => $employee->with([ 'identity' ]),
            'currency',
            'lines' => fn($line) => $line->with([
                'currency',
                'product.images',
                'variant' => fn($variant) => $variant
                    ->with([
                        'images',
                        'values',
                    ]),
                'invoiceLines' => fn($invoiceLine) => $invoiceLine
                    ->whereHas('invoice', fn($invoice) => $invoice->completed())
                    ->with([
                        'invoice',
                    ]),
            ]),
        ]);

        // redirect to list
        return view('sales::'.$this->prefix().'.orders.show', compact('resource'));
    }

    public final function update(Request $request, Resource $resource) {
        // cast values to boolean
        $request->merge([ 'is_purchase' => $this->isPurchaseDocument() ]);

        // start a transaction
        DB::beginTransaction();

        // associate Partner
        $resource->partnerable()->associate( $this->getPartnerable($request->partnerable_id) );

        // save resource
        if (!$resource->update( $request->input() ))
            // redirect with errors
            return back()->withInput()
                ->withErrors( $resource->errors() );

        // sync inventory lines
        if (($redirect = $this->syncLines($resource, $request->get('lines'))) !== true)
            // return redirection
            return $redirect;

        // confirm transaction
        DB::commit();

        // redirect to resource details
        return redirect()->route('backend.'.$this->prefix().'.orders.show', $resource);
    }

    public final function destroy(Resource $resource) {
        // delete resource
        if (!$resource->delete())
            // redirect with errors
            return back()
                ->withErrors($resource->errors()->any() ? $resource->errors() : [ $resource->getDocumentError() ]);

        // redirect to list
        return redirect()->route('backend.'.$this->prefix().'.orders');
    }

    public final function product(Request $request) {
        // find product/variant
        return response()->json([
            'product'   => Product::code( $request->product ),
            'variant'   => Variant::sku( $request->product ),
        ]);
    }

    public final function price(Request $request) {
        // get resources
        $product = $request->has('product') ? Product::findOrFail($request->product) : null;
        $variant = $request->has('variant') ? Variant::findOrFail($request->variant) : null;
        $currency = $request->has('currency') ? Currency::findOrFail($request->currency) : null;
        // return stock for requested product
        return response()->json($variant?->price($currency)?->pivot ?? $product?->price($currency)?->pivot);
    }

    private function syncLines(Resource $resource, array $lines) {
        // load inventory lines
        $resource->load([ 'lines' ]);

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
            }) ?? OrderLine::make([
                'order_id'      => $resource->id,
                'currency_id'   => $resource->currency_id,
                'employee_id'   => $resource->employee_id,
                'product_id'    => $product->id,
                'variant_id'    => $variant->id ?? null,
            ]);

            // update line values
            $orderLine->fill([
                'price_reference'   => $variant?->price( $resource->priceList )?->price->price ?? $product->price( $resource->priceList )?->price->price ?? $line['price'],
                'price_ordered'     => $line['price'],
                'quantity_ordered'  => $line['quantity'],
                // 'total'             => $line['total'],
            ]);
            // save inventory line
            if (!$orderLine->save())
                return back()->withInput()
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

}
