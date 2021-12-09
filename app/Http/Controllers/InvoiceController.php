<?php

namespace HDSSolutions\Laravel\Http\Controllers;

use App\Http\Controllers\Controller;
use HDSSolutions\Laravel\DataTables\Base\DataTableContract;
use HDSSolutions\Laravel\Http\Request;
use HDSSolutions\Laravel\Models\Invoice as Resource;
use HDSSolutions\Laravel\Models\Currency;
use HDSSolutions\Laravel\Models\Employee;
use HDSSolutions\Laravel\Models\InvoiceLine;
use HDSSolutions\Laravel\Models\Order;
use HDSSolutions\Laravel\Models\Product;
use HDSSolutions\Laravel\Models\Stamping;
use HDSSolutions\Laravel\Models\Variant;
use HDSSolutions\Laravel\Traits\CanProcessDocument;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Barryvdh\Snappy\Facades\SnappyPdf as PDF;

abstract class InvoiceController extends Controller {
    use CanProcessDocument;

    public function __construct() {
        // check resource Policy
        $this->authorizeResource(Resource::class, 'resource');
    }

    protected function documentClass():string {
        // return class
        return Resource::class;
    }

    protected final function redirectTo():string {
        // go to resource view
        return 'backend.'.$this->prefix().'.invoices.show';
    }

    protected abstract function documentType():string;

    protected final function isPurchaseDocument():bool { return $this->documentType() === 'purchase'; }
    protected final function isSaleDocument():bool { return $this->documentType() === 'sale'; }
    protected final function prefix():string { return Str::plural($this->documentType()); }

    // public abstract function index(Request $request, DataTableContract $dataTable);

    protected abstract function getPartnerable($partnerable);

    public final function store(Request $request) {
        // cast values to boolean
        if ($request->has('is_credit'))     $request->merge([ 'is_credit' => $request->is_credit == 'true' ]);
        // set is_purchase flag
        $request->merge([ 'is_purchase' => $this->isPurchaseDocument() ]);

        // start a transaction
        DB::beginTransaction();

        // create resource
        $resource = new Resource( $request->input() );

        // associate Partner
        $resource->partnerable()->associate( $this->getPartnerable($request->partnerable_id) );

        // save resource
        if (!$resource->save())
            // redirect with errors
            return back()->withInput()
                ->withErrors( $resource->errors() );

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
            view('backend::components.popup-callback', compact('resource')) :
            // redirect to resource details
            redirect()->route('backend.'.$this->prefix().'.invoices.show', $resource);
    }

    public final function show(Request $request, Resource $resource) {
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
            'receipments',
            'materialReturns' => fn($materialReturn) => $materialReturn->completed()->with([
                'lines.invoiceLine',
                'creditNote',
            ]),
        ]);

        // redirect to list
        return view('sales::'.$this->prefix().'.invoices.show', compact('resource'));
    }

    public final function update(Request $request, Resource $resource) {
        // cast values to boolean
        if ($request->has('is_credit'))     $request->merge([ 'is_credit' => $request->is_credit == 'true' ]);
        // set is_purchase flag
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
        return redirect()->route('backend.'.$this->prefix().'.invoices.show', $resource);
    }

    public final function destroy(Request $request, Resource $resource) {
        // delete resource
        if (!$resource->delete())
            // redirect with errors
            return back()
                ->withErrors($resource->errors()->any() ? $resource->errors() : [ $resource->getDocumentError() ]);

        // redirect to list
        return redirect()->route('backend.'.$this->prefix().'.invoices');
    }

    public final function printIt(Request $request, Resource $resource) {
        return view('sales::printables.'.$this->prefix().'.invoice', compact('resource'));
        // set global options
        PDF::setOptions([ 'dpi' => 150 ]);

        // render first pass
        $pdf = PDF::loadView('sales::printables.invoice', compact('resource'));
        $pdf->setOptions([
            'page-width'    => 85,
            'page-height'   => 25,
        ]);
        // get page count
        $pages = preg_match_all("/\/Page\W/", $pdf->output(), $dummy);

        // render new pass with optimal page height
        $pdf = PDF::loadView('sales::printables.invoice', compact('resource'));
        $pdf->setOptions([
            'page-width'    => 85,
            'page-height'   => 20 * $pages,
        ]);

        // return rendered pdf
        return $pdf->inline('testing_'.now());
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
                'price_reference'   => $variant?->price( $resource->priceList )?->pivot->price ?? $product->price( $resource->priceList )?->pivot->price ?? $line['price'],
                'price_invoiced'    => $line['price'],
                'quantity_invoiced' => $line['quantity'],
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
