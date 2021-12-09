<?php

namespace HDSSolutions\Laravel\Http\Controllers;

use HDSSolutions\Laravel\DataTables\SaleInvoicesDataTable as DataTable;
use HDSSolutions\Laravel\Http\Request;
use HDSSolutions\Laravel\Models\Invoice as Resource;
use HDSSolutions\Laravel\Models\Customer;
use HDSSolutions\Laravel\Models\Employee;
use HDSSolutions\Laravel\Models\Order;
use HDSSolutions\Laravel\Models\PriceList;
use HDSSolutions\Laravel\Models\Product;
use HDSSolutions\Laravel\Models\Stamping;
use HDSSolutions\Laravel\Models\Variant;

class SaleInvoiceController extends InvoiceController {

    protected function documentType():string { return 'sale'; }

    protected function getPartnerable($partnerable) {
        return Customer::findOrFail( $partnerable );
    }

    public function index(Request $request, DataTable $dataTable) {
        // check only-form flag
        if ($request->has('only-form'))
            // redirect to popup callback
            return view('backend::components.popup-callback', [ 'resource' => new Resource ]);

        // load resources
        if ($request->ajax()) return $dataTable->ajax();

        // load customers
        $customers = Customer::ordered()->with([
            // 'addresses', // TODO: Partnerable.addresses
        ])->get();

        // return view with dataTable
        return $dataTable->render('sales::sales.invoices.index', compact('customers') + [
            'count'                 => Resource::isSale()->count(),
            'show_company_selector' => !backend()->companyScoped(),
        ]);
    }

    public function create(Request $request) {
        // force company selection
        if (!backend()->companyScoped()) return view('backend::layouts.master', [ 'force_company_selector' => true ]);

        // load customers
        $customers = Customer::with([
            // 'addresses', // TODO: Partnerable.addresses
        ])->get();
        // load priceList
        $price_lists = PriceList::isSale()->ordered()->get();
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
        $stampings = Stamping::isSale()->ordered()->valid()->with([
            'invoices' => fn($invoice) => $invoice,
        ])->get();

        // get completed orders that aren't invoiced
        $orders = Order::isSale()->with([
            'partnerable' => fn($partnerable) => $partnerable->with([ 'identity' ]),
        ])->completed()->invoiced(false)->get();

        // show create form
        return view('sales::sales.invoices.create', compact(
            'customers',
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
        if ($resource->isApproved() || $resource->isProcessed())
            // redirect to show route
            return redirect()->route('backend.sales.invoices.show', $resource);

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
        $customers = Customer::with([
            // 'addresses', // TODO: Customer.addresses
        ])->get();
        // load priceList
        $price_lists = PriceList::isSale()->ordered()->get();
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
        // load valid stampings
        $stampings = Stamping::ordered()->valid()->with([
            'invoices' => fn($invoice) => $invoice,
        ])->get();

        // get completed orders
        $orders = Order::with([
            'partnerable' => fn($partnerable) => $partnerable->with([ 'identity' ]),
        ])->completed()->invoiced(false)->get();

        // show edit form
        return view('sales::sales.invoices.edit', compact('resource',
            'customers',
            'price_lists',
            'branches',
            'employees',
            'products',
            'stampings',
            'orders',
        ));
    }

}
