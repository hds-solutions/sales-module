<?php

namespace HDSSolutions\Laravel\Http\Controllers;

use App\Http\Controllers\Controller;
use HDSSolutions\Laravel\Reports\SaleInvoicesReport;
use HDSSolutions\Laravel\Http\Request;
use HDSSolutions\Laravel\Models\Invoice as Resource;
use HDSSolutions\Laravel\Models\Customer;

class SalesReportsController extends Controller {

    public function sale_invoices(Request $request, SaleInvoicesReport $report) {
        // load resources
        if ($request->ajax()) return $report->ajax();

        // load filters
        $customers = Customer::ordered()->get();

        // return view with report
        return $report->render('sales::reports.sales.invoices', compact(
            'customers',
        ) + [
            'count'                 => Resource::isSale()->count(),
            'show_company_selector' => !backend()->companyScoped(),
        ]);
    }

}
