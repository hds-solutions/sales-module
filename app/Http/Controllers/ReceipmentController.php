<?php

namespace HDSSolutions\Laravel\Http\Controllers;

use App\Http\Controllers\Controller;
use HDSSolutions\Laravel\DataTables\ReceipmentDataTable as DataTable;
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

class ReceipmentController extends Controller {
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
        return 'backend.receipments.show';
    }

    public function index(Request $request, DataTable $dataTable) {
        // check only-form flag
        if ($request->has('only-form'))
            // redirect to popup callback
            return view('backend::components.popup-callback', [ 'resource' => new Resource ]);

        // load resources
        if ($request->ajax()) return $dataTable->ajax();

        // return view with dataTable
        return $dataTable->render('sales::receipments.index', [
            'count'                 => Resource::count(),
            'show_company_selector' => !backend()->companyScoped(),
        ]);
    }

    public function create(Request $request) {
        // force company selection
        if (!backend()->companyScoped()) return view('backend::layouts.master', [ 'force_company_selector' => true ]);

        // redirect to payment window
        return redirect()->route('backend.payment');

        // load employees
        $employees = Employee::all();
        // load customers
        $customers = Customer::with([
            // 'addresses', // TODO: Customer.addresses
            // load available CreditNotes of Customer
            'creditNotes' => fn($creditNote) => $creditNote->available()->with([ 'identity' ]),
        ])->get();

        // get completed invoices that aren't paid
        $invoices = Invoice::with([
            'partnerable' => fn($partnerable) => $partnerable->with([ 'identity' ]),
        ])->completed()->paid(false)->get();

        $highs = [
            'document_number'   => Resource::nextDocumentNumber(),
        ];

        // show create form
        return view('sales::receipments.create', compact(
            'employees',
            'customers',
            'invoices',
            'highs',
        ));
    }

    public function store(Request $request) {
        // cast values to boolean
        if ($request->has('is_purchase'))   $request->merge([ 'is_purchase' => $request->is_purchase == 'true' ]);

        // start a transaction
        DB::beginTransaction();
dump($request->input());

        // create resource
        $resource = new Resource( $request->input() );
        $resource->partnerable()->associate( Customer::findOrFail($request->partnerable_id) );

        // save resource
        if (!$resource->save())
            // redirect with errors
            return back()
                ->withErrors( $resource->errors() )
                ->withInput();

        // sync receipment invoices
        if (($redirect = $this->syncInvoices($resource, $request->get('invoices'))) !== true)
            // return redirection
            return $redirect;

        // sync receipment payments
        if (($redirect = $this->syncPayments($resource, $request->get('payments'))) !== true)
            // return redirection
            return $redirect;

dump($resource->load([ 'invoices', 'cashLines', 'cards', 'checks' ]));
dump($resource->invoices_amount);
dump($resource->payments_amount);
return;

        // confirm transaction
        DB::commit();

        // check return type
        return $request->has('only-form') ?
            // redirect to popup callback
            view('backend::components.popup-callback', compact('resource')) :
            // redirect to resource details
            redirect()->route('backend.receipments.show', $resource);
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\Resource $resource
     * @return \Illuminate\Http\Response
     */
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
        return view('sales::receipments.show', compact('resource'));
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
            return redirect()->route('backend.receipments.show', $resource);

        // load employees
        $employees = Employee::all();
        // load customers
        $customers = Customer::with([
            // 'addresses', // TODO: Customer.addresses
            // load available CreditNotes of Customer
            'creditNotes' => fn($creditNote) => $creditNote->available()->with([ 'identity' ]),
        ])->get();

        // get completed invoices that aren't paid
        $invoices = Invoice::with([
            'partnerable' => fn($partnerable) => $partnerable->with([ 'identity' ]),
        // ])->completed()->paid(false)->get();
        ])->completed()->get();

        // load resource relations
        $resource->load([
            'currency',
            // 'lines.product',
        ]);

        // show edit form
        return view('sales::receipments.edit', compact('resource',
            'employees',
            'customers',
            'invoices',
        ));
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

        // sync receipment invoices
        if (($redirect = $this->syncInvoices($resource, $request->get('invoices'))) !== true)
            // return redirection
            return $redirect;

        // sync receipment payments
        if (($redirect = $this->syncPayments($resource, $request->get('payments'))) !== true)
            // return redirection
            return $redirect;
dump($resource);
return;
        // confirm transaction
        DB::commit();

        // redirect to resource details
        return redirect()->route('backend.receipments.show', $resource);
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
        return redirect()->route('backend.receipments');
    }

    private function syncInvoices(Resource $resource, array $invoices) {
        // load receipment invoices
        $resource->load([ 'invoices' ]);

        // foreach new/updated invoices
        foreach (($invoices = array_group( $invoices )) as $invoiceLine) {
            // ignore line if invoice wasn't specified
            if (!isset($invoiceLine['invoice_id']) || is_null($invoiceLine['imputed_amount'])) continue;

            // load invoice
            $invoice = Invoice::find($invoiceLine['invoice_id']);

            // find existing line
            $receipmentInvoice = $resource->invoices->first(fn($iLine) => $iLine->invoice_id == $invoice->id)
            // create a new line
            ?? ReceipmentInvoice::make([
                'receipment_id'     => $resource->id,
                'invoice_id'        => $invoice->id,
            ]);

            // update line values
            $receipmentInvoice->fill([
                'imputed_amount'    => $invoiceLine['imputed_amount'],
            ]);

            // save receipment line
            if (!$receipmentInvoice->save())
                return back()
                    ->withInput()
                    ->withErrors( $receipmentInvoice->errors() );
        }

        // find removed receipment invoices
        foreach ($resource->invoices as $invoice) {
            // deleted flag
            $deleted = true;
            // check against $request->invoices
            foreach ($invoices as $rLine) {
                // ignore empty invoices
                if (!isset($rLine['invoice_id'])) continue;
                // check if line exists
                if ($invoice->id == $rLine['invoice_id'])
                    // change flag to keep line
                    $deleted = false;
            }
            // remove line if was deleted
            if ($deleted) $invoice->delete();
        }

        // return success
        return true;
    }

    private function syncPayments(Resource $resource, array $payments) {
        // load receipment payments
        $resource->load([ 'cashLines', 'cards', 'credits', 'creditNotes', 'checks', 'promissoryNotes' ]);

        // foreach new/updated payments
        foreach (($payments = array_group( $payments )) as $paymentLine) {
            // ignore line if invoice wasn't specified
            if (!isset($paymentLine['payment_type']) || is_null($paymentLine['payment_amount'])) continue;

dump($paymentLine);
continue;

            // find existing line
            $receipmentPayment = $resource->payments->first(fn($pLine) => $pLine->invoice_id == $invoice->id)
            // create a new line
            ?? ReceipmentInvoice::make([
                'receipment_id'     => $resource->id,
                'invoice_id'        => $invoice->id,
            ]);

            // update line values
            $receipmentPayment->fill([
                'imputed_amount'    => $paymentLine['imputed_amount'],
            ]);

            // save receipment line
            if (!$receipmentPayment->save())
                return back()
                    ->withInput()
                    ->withErrors( $receipmentPayment->errors() );
        }
return true;

        // find removed receipment payments
        foreach ($resource->payments as $invoice) {
            // deleted flag
            $deleted = true;
            // check against $request->payments
            foreach ($payments as $rLine) {
                // ignore empty payments
                if (!isset($rLine['invoice_id'])) continue;
                // check if line exists
                if ($invoice->id == $rLine['invoice_id'])
                    // change flag to keep line
                    $deleted = false;
            }
            // remove line if was deleted
            if ($deleted) $invoice->delete();
        }

        // return success
        return true;
    }

}
