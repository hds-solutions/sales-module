<?php

namespace HDSSolutions\Laravel\Http\Controllers;

use App\Http\Controllers\Controller;
use HDSSolutions\Laravel\DataTables\SaleReceipmentsDataTable as DataTable;
use HDSSolutions\Laravel\Http\Request;
use HDSSolutions\Laravel\Models\Receipment as Resource;
use HDSSolutions\Laravel\Models\Currency;
use HDSSolutions\Laravel\Models\Customer;
use HDSSolutions\Laravel\Models\Employee;
use HDSSolutions\Laravel\Models\Invoice;
use HDSSolutions\Laravel\Models\ReceipmentInvoice;
use HDSSolutions\Laravel\Models\ReceipmentPayment;
use HDSSolutions\Laravel\Models\Product;
use HDSSolutions\Laravel\Models\Variant;
use HDSSolutions\Laravel\Traits\CanProcessDocument;
use Illuminate\Support\Facades\DB;

class SaleReceipmentController extends Controller {

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
        return $dataTable->render('sales::sales.receipments.index', [
            'count'                 => Resource::isSale()->count(),
            'show_company_selector' => !backend()->companyScoped(),
        ]);
    }

    public function show(Resource $resource) {
        // load receipment data
        $resource->load([
            'employee',
            'partnerable',

            'invoices',

            'cashLines',
            'cards',
            'credits',
            'creditNotes',
            'checks' => fn($check) => $check->with([ 'receipmentPayment.creditNote' ]),
            'promissoryNotes',
        ]);

        // redirect to list
        return view('sales::sales.receipments.show', compact('resource'));
    }

}
