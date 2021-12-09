<?php

namespace HDSSolutions\Laravel\Http\Controllers;

use HDSSolutions\Laravel\DataTables\SaleOrdersDataTable as DataTable;
use HDSSolutions\Laravel\Http\Request;
use HDSSolutions\Laravel\Models\Order as Resource;
use HDSSolutions\Laravel\Models\Customer;
use HDSSolutions\Laravel\Models\Employee;
use HDSSolutions\Laravel\Models\PriceList;
use HDSSolutions\Laravel\Models\Product;

class SaleOrderController extends OrderController {

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
        return $dataTable->render('sales::sales.orders.index', compact('customers') + [
            'count'                 => Resource::isSale()->count(),
            'show_company_selector' => !backend()->companyScoped(),
        ]);
    }

    public function create(Request $request) {
        // force company selection
        if (!backend()->companyScoped()) return view('backend::layouts.master', [ 'force_company_selector' => true ]);

        // load customers
        $customers = Customer::ordered()->with([
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
        ])->get();

        $highs = [
            'document_number'   => Resource::nextDocumentNumber() ?? '00000001',
        ];

        // show create form
        return view('sales::sales.orders.create', compact(
            'customers',
            'price_lists',
            'branches',
            'employees',
            'products',
            'highs',
        ));
    }

    public function edit(Resource $resource) {
        // check if document is already approved or processed
        if ($resource->isApproved() || $resource->wasProcessed())
            // redirect to show route
            return redirect()->route('backend.sales.orders.show', $resource);

        // load resource relations
        $resource->load([
            'currency',
            'lines.product',
        ]);

        // load customers
        $customers = Customer::ordered()->get();
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
        ])->get();

        // show edit form
        return view('sales::sales.orders.edit', compact('resource',
            'customers',
            'price_lists',
            'branches',
            'employees',
            'products',
        ));
    }

}
