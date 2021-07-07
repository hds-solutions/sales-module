@extends('sales::layouts.master')

@section('page-name', __('sales::receipments.title'))
@section('description', __('sales::receipments.description'))

@section('content')

<div class="card mb-3">
    <div class="card-header">
        <div class="row">
            <div class="col-6">
                <i class="fas fa-user-plus"></i>
                @lang('sales::receipments.show')
            </div>
            <div class="col-6 d-flex justify-content-end">
                {{-- @if (!$resource->isCompleted())
                <a href="{{ route('backend.receipments.edit', $resource) }}"
                    class="btn btn-sm ml-2 btn-info">@lang('sales::receipments.edit')</a>
                @endif --}}
                <a href="{{ route('backend.receipments.create') }}"
                    class="btn btn-sm ml-2 btn-primary">@lang('sales::receipments.create')</a>
            </div>
        </div>
    </div>
    <div class="card-body">

        @include('backend::components.errors')

        <div class="row">
            <div class="col-12 col-xl-6">

                <div class="row">
                    <div class="col">
                        <h2>@lang('sales::receipment.details.0')</h2>
                    </div>
                </div>

                <div class="row">
                    <div class="col">@lang('sales::invoice.document_number.0'):</div>
                    <div class="col h4 font-weight-bold">{{ $resource->document_number }}</div>
                </div>

                <div class="row">
                    <div class="col">@lang('sales::invoice.partnerable_id.0'):</div>
                    <div class="col h4 font-weight-bold">{{ $resource->partnerable->fullname }} <small class="font-weight-light">[{{ $resource->partnerable->ftid }}]</small></div>
                </div>

                <div class="row">
                    <div class="col">@lang('sales::order.employee_id.0'):</div>
                    <div class="col h4">{{ $resource->employee->fullname }}</div>
                </div>

                <div class="row">
                    <div class="col">@lang('sales::invoice.currency_id.0'):</div>
                    <div class="col h4">{{ currency($resource->currency_id)->name }}</div>
                </div>

                <div class="row">
                    <div class="col">@lang('sales::receipment.transacted_at.0'):</div>
                    <div class="col h4">{{ pretty_date($resource->transacted_at, true) }}</div>
                </div>

                <div class="row">
                    <div class="col">@lang('sales::receipment.document_status.0'):</div>
                    <div class="col h4 mb-0">{{ Document::__($resource->document_status) }}</div>
                </div>

            </div>
        </div>

        <div class="row pt-5">
            <div class="col-6 pr-5">

                <div class="row">
                    <div class="col">
                        <h2 class="mb-0">@lang('sales::receipment.invoices.0')</h2>
                    </div>
                </div>

                <div class="row">
                    <div class="col">

                        <div class="table-responsive">
                            <table class="table table-sm table-striped table-borderless table-hover" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th class="align-middle">{{-- @lang('sales::invoice.document_number.0') --}}</th>
                                        <th class="align-middle text-right">@lang('sales::invoice.total.0')</th>
                                        <th class="align-middle text-right">@lang('sales::invoice.paid_amount.0')</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @foreach ($resource->invoices as $invoice)
                                        <tr>
                                            <td class="align-middle">
                                                <a href="{{ route('backend.invoices.show', $invoice) }}"
                                                    class="text-secondary text-decoration-none font-weight-bold">{{ $invoice->document_number }}<small class="ml-2">{{ $invoice->transacted_at_pretty }}</small></a>
                                            <td class="align-middle text-right">{{ currency($invoice->currency_id)->code }} <b>{{ number($invoice->total, currency($invoice->currency_id)->decimals) }}</b></td>
                                            <td class="align-middle text-right">{{ currency($invoice->currency_id)->code }} <b>{{ number($invoice->receipmentInvoice->imputed_amount, currency($invoice->currency_id)->decimals) }}</b></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>

                <div class="row">
                    <div class="col text-right">
                        <h5 class="pr-1 mb-0">{{ currency($resource->currency_id)->code }} <b>{{ number($resource->invoices_amount, currency($resource->currency_id)->decimals) }}</b></h5>
                    </div>
                </div>

            </div>

            <div class="col-6 pl-0">

                <div class="row">
                    <div class="col">
                        <h2 class="mb-0">@lang('sales::receipment.payments.0')</h2>
                    </div>
                </div>

                <div class="row">
                    <div class="col">

                        <div class="table-responsive">
                            <table class="table table-sm table-striped table-borderless table-hover" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th class="align-middle">{{-- @lang('sales::receipment.payments.payment_type.0') --}}</th>
                                        <th class="align-middle">{{-- @lang('sales::receipment.payments.description.0') --}}</th>
                                        <th class="align-middle text-right">@lang('sales::receipment.payments.payment_amount.0')</th>
                                        <th class="align-middle text-right">@lang('sales::receipment.payments.used_amount.0')</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @foreach ($resource->payments as $payment)
                                        <tr>
                                            <td class="align-middle">{{ __(Payment::PAYMENT_TYPES[$payment->receipmentPayment->payment_type]) }}</td>
                                            <td class="align-middle">
                                                <a href="{{ match($payment->receipmentPayment->payment_type) {
                                                    Payment::PAYMENT_TYPE_Cash          => route('backend.cashes.show', $payment->cash),
                                                    Payment::PAYMENT_TYPE_Card          => '#',
                                                    Payment::PAYMENT_TYPE_Credit        => '#',
                                                    Payment::PAYMENT_TYPE_Check         => route('backend.checks.show', $payment),
                                                    Payment::PAYMENT_TYPE_CreditNote    => route('backend.credit_notes.show', $payment),
                                                    Payment::PAYMENT_TYPE_Promissory    => '#',
                                                    default => null,
                                                } }}" class="text-secondary text-decoration-none"><b>{!! match($payment->receipmentPayment->payment_type) {
                                                    Payment::PAYMENT_TYPE_Cash          => $payment->cash->cashBook->name,
                                                    Payment::PAYMENT_TYPE_Card          => $payment->card_holder.' <small>**** **** **** '.$payment->card_number.'</small>',
                                                    Payment::PAYMENT_TYPE_Credit        => trans_choice('sales::receipment.payments.dues.0', $payment->dues, [ 'dues' => $payment->dues ]).' <small>'.$payment->interest.'%</small>',
                                                    Payment::PAYMENT_TYPE_Check         => $payment->document_number.'<small class="ml-2">'.$payment->bank_name.'</small>',
                                                    Payment::PAYMENT_TYPE_CreditNote    => $payment->document_number.'<small class="ml-2">'.$payment->payment_amount.'</small>',
                                                    default => null,
                                                } !!}</b></a>
                                            </td>
                                            <td class="align-middle text-right">{{ currency($payment->receipmentPayment->currency_id)->code }} <b>{{ number($payment->receipmentPayment->payment_amount, currency($payment->receipmentPayment->currency_id)->decimals) }}</b></td>
                                            <td class="align-middle text-right">{{ currency($payment->receipmentPayment->currency_id)->code }} <b>{{ number($payment->receipmentPayment->payment_amount - $payment->receipmentPayment->creditNote?->payment_amount, currency($payment->receipmentPayment->currency_id)->decimals) }}</b></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>

                <div class="row">
                    <div class="col text-right">
                        <h5 class="pr-1 mb-0">{{ currency($resource->currency_id)->code }} <b>{{ number($resource->payments_amount, currency($resource->currency_id)->decimals) }}</b></h5>
                    </div>
                </div>

            </div>
        </div>

        @include('backend::components.document-actions', [
            'route'     => 'backend.receipments.process',
            'resource'  => $resource,
        ])

    </div>
</div>

@endsection
