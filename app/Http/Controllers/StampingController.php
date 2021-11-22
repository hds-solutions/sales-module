<?php

namespace HDSSolutions\Laravel\Http\Controllers;

use App\Http\Controllers\Controller;
use HDSSolutions\Laravel\DataTables\StampingDataTable as DataTable;
use HDSSolutions\Laravel\Http\Request;
use HDSSolutions\Laravel\Models\Stamping as Resource;
use HDSSolutions\Laravel\Models\Provider;

class StampingController extends Controller {

    public function __construct() {
        // check resource Policy
        $this->authorizeResource(Resource::class, 'resource');
    }

    public function index(Request $request, DataTable $dataTable) {
        // check only-form flag
        if ($request->has('only-form'))
            // redirect to popup callback
            return view('backend::components.popup-callback', [ 'resource' => new Resource ]);

        // load resources
        if ($request->ajax()) return $dataTable->ajax();

        // return view with dataTable
        return $dataTable->render('sales::stampings.index', [
            'count'                 => Resource::count(),
            'show_company_selector' => !backend()->companyScoped(),
        ]);
    }

    public function create(Request $request) {
        // force company selection
        if (!backend()->companyScoped()) return view('backend::layouts.master', [ 'force_company_selector' => true ]);

        // get providers
        $providers = Provider::ordered()->get();

        // show create form
        return view('sales::stampings.create', compact('providers'));
    }

    public function store(Request $request) {
        // cast to boolean
        if ($request->has('is_purchase'))   $request->merge([ 'is_purchase' => filter_var($request->is_purchase, FILTER_VALIDATE_BOOLEAN) ]);

        // create resource
        $resource = new Resource( $request->input() );

        // save resource
        if (!$resource->save())
            // redirect with errors
            return back()->withInput()
                ->withErrors( $resource->errors() );

        // check return type
        return $request->has('only-form') ?
            // redirect to popup callback
            view('backend::components.popup-callback', compact('resource')) :
            // redirect to resources list
            redirect()->route('backend.stampings');
    }

    public function show(Request $request, Resource $resource) {
        // redirect to list
        return redirect()->route('backend.stampings');
    }

    public function edit(Request $request, Resource $resource) {
        // get providers
        $providers = Provider::ordered()->get();

        // show edit form
        return view('sales::stampings.edit', compact('resource', 'providers'));
    }

    public function update(Request $request, Resource $resource) {
        // cast to boolean
        if ($request->has('is_purchase'))   $request->merge([ 'is_purchase' => filter_var($request->is_purchase, FILTER_VALIDATE_BOOLEAN) ]);

        // save resource
        if (!$resource->update( $request->input() ))
            // redirect with errors
            return back()->withInput()
                ->withErrors( $resource->errors() );

        // redirect to list
        return redirect()->route('backend.stampings');
    }

    public function destroy(Request $request, Resource $resource) {
        // delete resource
        if (!$resource->delete())
            // redirect with errors
            return back()
                ->withErrors( $resource->errors() );

        // redirect to list
        return redirect()->route('backend.stampings');
    }

}
