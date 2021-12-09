<?php

namespace HDSSolutions\Laravel\Http\Controllers;

use HDSSolutions\Laravel\DataTables\PurchaseInvoicesDataTable as DataTable;
use HDSSolutions\Laravel\Http\Request;
use HDSSolutions\Laravel\Models\Invoice as Resource;
use HDSSolutions\Laravel\Models\Employee;
use HDSSolutions\Laravel\Models\Product;
use HDSSolutions\Laravel\Models\PriceList;
use HDSSolutions\Laravel\Models\Provider;
use HDSSolutions\Laravel\Models\Order;
use HDSSolutions\Laravel\Models\Stamping;

class PurchaseInvoiceController extends InvoiceController {

    protected function documentType():string { return 'purchase'; }

    protected function getPartnerable($partnerable) {
        return Provider::findOrFail( $partnerable );
    }

    public function index(Request $request, DataTable $dataTable) {
        // check only-form flag
        if ($request->has('only-form'))
            // redirect to popup callback
            return view('backend::components.popup-callback', [ 'resource' => new Resource ]);

        // load resources
        if ($request->ajax()) return $dataTable->ajax();

        // load providers
        $providers = Provider::ordered()->with([
            // 'addresses', // TODO: Partnerable.addresses
        ])->get();

        // return view with dataTable
        return $dataTable->render('sales::purchases.invoices.index', compact('providers') + [
            'count'                 => Resource::isPurchase()->count(),
            'show_company_selector' => !backend()->companyScoped(),
        ]);
    }

    public function create(Request $request) {
        // force company selection
        if (!backend()->companyScoped()) return view('backend::layouts.master', [ 'force_company_selector' => true ]);

        // load providers
        $providers = Provider::ordered()->with([
            // 'addresses', // TODO: Provider.addresses
        ])->get();
        // load priceList
        $price_lists = PriceList::isPurchase()->ordered()->get();
        // load current company branches with warehouses
        $branches = backend()->company()->branches()->with([
            'warehouses',
        ])->get();
        // load employees
        $employees = Employee::ordered()->get();
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
        // load valid stampings
        $stampings = Stamping::isPurchase()->ordered()->valid()->with([
            'invoices' => fn($invoice) => $invoice,
        ])->get();

        // get completed orders that aren't invoiced
        $orders = Order::isPurchase()->with([
            'partnerable' => fn($partnerable) => $partnerable->with([ 'identity' ]),
        ])->completed()->invoiced(false)->get();

        // show create form
        return view('sales::purchases.invoices.create', compact(
            'providers',
            'price_lists',
            'branches',
            'employees',
            'products',
            'stampings',
            'orders',
        ));
    }

    public function edit(Request $request, Resource $resource) {
        // check if document is already approved or processed
        if ($resource->isApproved() || $resource->wasProcessed())
            // redirect to show route
            return redirect()->route('backend.purchases.invoices.show', $resource);

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

        // load providers
        $providers = Provider::ordered()->with([
            // 'addresses', // TODO: Provider.addresses
        ])->get();
        // load priceList
        $price_lists = PriceList::isPurchase()->ordered()->get();
        // load current company branches with warehouses
        $branches = backend()->company()->branches()->with([
            'warehouses',
        ])->get();
        // load employees
        $employees = Employee::ordered()->get();
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
        // load valid stampings
        $stampings = Stamping::isPurchase()->ordered()->valid()->with([
            'invoices' => fn($invoice) => $invoice,
        ])->get();

        // get completed orders
        $orders = Order::isPurchase()->with([
            'partnerable' => fn($partnerable) => $partnerable->with([ 'identity' ]),
        ])->completed()->invoiced(false)->get();

        // show edit form
        return view('sales::purchases.invoices.edit', compact('resource',
            'providers',
            'price_lists',
            'branches',
            'employees',
            'products',
            'stampings',
            'orders',
        ));
    }

}
