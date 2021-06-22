@extends('sales::layouts.master')

@section('page-name', __('sales::invoices.title'))
@section('description', __('sales::invoices.description'))

@section('content')

<div class="card mb-3">
    <div class="card-header">
        <div class="row">
            <div class="col-6">
                <i class="fas fa-user-plus"></i>
                @lang('sales::invoices.show')
            </div>
            <div class="col-6 d-flex justify-content-end">
                @if (!$resource->isCompleted())
                <a href="{{ route('backend.invoices.edit', $resource) }}"
                    class="btn btn-sm ml-2 btn-info">@lang('sales::invoices.edit')</a>
                @endif
                <a href="{{ route('backend.invoices.create') }}"
                    class="btn btn-sm ml-2 btn-primary">@lang('sales::invoices.create')</a>
            </div>
        </div>
    </div>
    <div class="card-body">

        @include('backend::components.errors')

        <div class="row">
            <div class="col">
                <h2>@lang('sales::invoice.details.0')</h2>
            </div>
        </div>

        <div class="row">
            <div class="col-12">

                <div class="row">
                    <div class="col-4 col-lg-4">@lang('sales::invoice.stamping.0'):</div>
                    <div class="col-8 col-lg-6 h4">{{ $resource->stamping }}</div>
                </div>

                <div class="row">
                    <div class="col-4 col-lg-4">@lang('sales::invoice.document_number.0'):</div>
                    <div class="col-8 col-lg-6 h4 font-weight-bold">{{ $resource->document_number }}</div>
                </div>

                <div class="row">
                    <div class="col-4 col-lg-4">@lang('sales::invoice.branch_id.0'):</div>
                    <div class="col-8 col-lg-6 h4">{{ $resource->branch->name }}</div>
                </div>

                <div class="row">
                    <div class="col-4 col-lg-4">@lang('sales::invoice.partnerable_id.0'):</div>
                    <div class="col-8 col-lg-6 h4 font-weight-bold">{{ $resource->partnerable->fullname }} <small class="font-weight-light">[{{ $resource->partnerable->ftid }}]</small></div>
                </div>

                <div class="row">
                    <div class="col-4 col-lg-4">@lang('sales::order.employee_id.0'):</div>
                    <div class="col-8 col-lg-6 h4">{{ $resource->employee->fullname }}</div>
                </div>

                <div class="row">
                    <div class="col-4 col-lg-4">@lang('sales::invoice.currency_id.0'):</div>
                    <div class="col-8 col-lg-6 h4">{{ currency($resource->currency_id)->name }}</div>
                </div>

                {{-- <div class="row">
                    <div class="col-4 col-lg-4">@lang('sales::invoice.description.0'):</div>
                    <div class="col-8 col-lg-6 h4">{{ $resource->description }}</div>
                </div> --}}

                <div class="row">
                    <div class="col-4 col-lg-4">@lang('sales::invoice.transacted_at.0'):</div>
                    <div class="col-8 col-lg-6 h4">{{ pretty_date($resource->transacted_at, true) }}</div>
                </div>

                <div class="row">
                    <div class="col-4 col-lg-4">@lang('sales::invoice.document_status.0'):</div>
                    <div class="col-8 col-lg-6 h4">{{ Document::__($resource->document_status) }}</div>
                </div>

            </div>
        </div>

        <div class="row">
            <div class="col">
                <h2>@lang('sales::invoice.lines.0')</h2>
            </div>
        </div>

        <div class="row">
            <div class="col">

                <div class="table-responsive">
                    <table class="table table-sm table-striped table-borderless table-hover" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th class="w-150px">@lang('sales::invoice.lines.image.0')</th>
                                <th>@lang('sales::invoice.lines.product_id.0')</th>
                                <th>@lang('sales::invoice.lines.variant_id.0')</th>
                                <th class="w-150px text-center">@lang('sales::invoice.lines.price_invoiced.0')</th>
                                <th class="w-150px text-center">@lang('sales::invoice.lines.quantity_invoiced.0')</th>
                                @if ($resource->is_purchase) <th class="w-150px text-center">@lang('sales::invoice.lines.quantity_received.0')</th> @endif
                                <th class="w-150px text-center">@lang('sales::invoice.lines.total.0')</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($resource->lines as $line)
                                <tr data-toggle="collapse" data-target=".line-{{ $line->id }}-details">
                                    <td>
                                        <div class="d-flex justify-content-center">
                                            <img src="{{ asset(
                                                // has variant and variant has images
                                                $line->variant !== null && $line->variant->images->count() ?
                                                // first variant image
                                                $line->variant->images->first()->url :
                                                // first product image or default as fallback
                                                ($line->product->images->first()->url ?? 'backend-module/assets/images/default.jpg')
                                            ) }}" class="img-fluid mh-50px">
                                        </div>
                                    </td>
                                    <td class="align-middle pl-3">
                                        <a href="{{ route('backend.products.edit', $line->product) }}"
                                            class="text-primary text-decoration-none">{{ $line->product->name }}</a>
                                    </td>
                                    <td class="align-middle pl-3">
                                        <div>
                                            @if ($line->variant)
                                            <a href="{{ route('backend.variants.edit', $line->variant) }}"
                                                class="text-primary text-decoration-none">{{ $line->variant->sku }}</a>
                                            @else
                                                --
                                            @endif
                                        </div>
                                        @if ($line->variant && $line->variant->values->count())
                                        <div class="small pl-2">
                                            @foreach($line->variant->values as $value)
                                                @if ($value->option_value === null) @continue @endif
                                                <div>{{ $value->option->name }}: <b>{{ $value->option_value->value }}</b></div>
                                            @endforeach
                                        </div>
                                        @endif
                                    </td>
                                    <td class="align-middle text-right h6">{{ currency($line->currency_id)->code }} <b>{{ number($line->price_invoiced, currency($line->currency_id)->decimals) }}</b></td>
                                    <td class="align-middle text-center h4 font-weight-bold">{{ $line->quantity_invoiced }}</td>
                                    @if ($resource->is_purchase) <td class="align-middle text-center h5">{{ $line->quantity_received ?? '--' }}</td> @endif
                                    <td class="align-middle text-right h5 w-100px">{{ currency($line->currency_id)->code }} <b>{{ number($line->total, currency($line->currency_id)->decimals) }}</b></td>
                                </tr>
                                @foreach ($line->orderLines as $orderLine)
                                <tr class="d-none"></tr>
                                <tr class="collapse line-{{ $line->id }}-details">
                                    <td class="py-0"></td>
                                    <td class="py-0 pl-3" colspan="3">
                                        <a href="{{ route('backend.orders.show', $orderLine->order) }}"
                                            class="text-secondary text-decoration-none">{{ $orderLine->order->document_number }}</a> <small class="ml-1">{{ $orderLine->order->transacted_at_pretty }}</small>
                                    </td>
                                    <td class="py-0 text-center">{{ $orderLine->pivot->quantity_ordered }}</td>
                                    <td class="py-0"></td>
                                </tr>
                                @endforeach
                            @endforeach
                        </tbody>
                    </table>
                </div>

            </div>
        </div>

        <div class="row">
            <div class="col-2 offset-8 font-weight-bold d-flex align-items-center justify-content-end">Total</div>
            <div class="col-2 text-right">
                <h3 class="pr-1 m-0">{{ currency($resource->currency_id)->code }} <b>{{ number($resource->total, currency($resource->currency_id)->decimals) }}</b></h3>
            </div>
        </div>

        @include('backend::components.document-actions', [
            'route'     => 'backend.invoices.process',
            'resource'  => $resource,
        ])

    </div>
</div>

@endsection
