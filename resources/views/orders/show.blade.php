@extends('sales::layouts.master')

@section('page-name', __('sales::orders.title'))
@section('description', __('sales::orders.description'))

@section('content')

<div class="card mb-3">
    <div class="card-header">
        <div class="row">
            <div class="col-6">
                <i class="fas fa-user-plus"></i>
                @lang('sales::orders.show')
            </div>
            <div class="col-6 d-flex justify-content-end">
                @if (!$resource->isCompleted())
                <a href="{{ route('backend.orders.edit', $resource) }}"
                    class="btn btn-sm ml-2 btn-info">@lang('sales::orders.edit')</a>
                @endif
                <a href="{{ route('backend.orders.create') }}"
                    class="btn btn-sm ml-2 btn-primary">@lang('sales::orders.create')</a>
            </div>
        </div>
    </div>
    <div class="card-body">

        @include('backend::components.errors')

        <div class="row">
            <div class="col">
                <h2>@lang('sales::order.details.0')</h2>
            </div>
        </div>

        <div class="row">
            <div class="col-12">

                <div class="row">
                    <div class="col-4 col-lg-4">@lang('sales::order.document_number.0'):</div>
                    <div class="col-8 col-lg-6 h4 font-weight-bold">{{ $resource->document_number }}</div>
                </div>

                <div class="row">
                    <div class="col-4 col-lg-4">@lang('sales::order.branch_id.0') / @lang('sales::order.warehouse_id.0'):</div>
                    <div class="col-8 col-lg-6 h4">{{ $resource->branch->name }} / {{ $resource->warehouse->name }}</div>
                </div>

                <div class="row">
                    <div class="col-4 col-lg-4">@lang('sales::order.partnerable_id.0'):</div>
                    <div class="col-8 col-lg-6 h4">{{ $resource->partnerable->fullname }} <small class="font-weight-light">[{{ $resource->partnerable->ftid }}]</small></div>
                </div>

                <div class="row">
                    <div class="col-4 col-lg-4">@lang('sales::order.employee_id.0'):</div>
                    <div class="col-8 col-lg-6 h4">{{ $resource->employee->fullname }}</div>
                </div>

                <div class="row">
                    <div class="col-4 col-lg-4">@lang('sales::order.currency_id.0'):</div>
                    <div class="col-8 col-lg-6 h4">{{ $resource->currency->name }}</div>
                </div>

                <div class="row">
                    <div class="col-4 col-lg-4">@lang('sales::order.transacted_at.0'):</div>
                    <div class="col-8 col-lg-6 h4">{{ pretty_date($resource->transacted_at, true) }}</div>
                </div>

                <div class="row">
                    <div class="col-4 col-lg-4">@lang('sales::order.document_status.0'):</div>
                    <div class="col-8 col-lg-6 h4">{{ Document::__($resource->document_status) }}</div>
                </div>

            </div>
        </div>

        <div class="row">
            <div class="col">
                <h2>@lang('sales::order.lines.0')</h2>
            </div>
        </div>

        <div class="row">
            <div class="col">

                <div class="table-responsive">
                    <table class="table table-sm table-striped table-borderless table-hover" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th class="w-150px">@lang('sales::order.lines.image.0')</th>
                                <th>@lang('sales::order.lines.product_id.0')</th>
                                <th>@lang('sales::order.lines.variant_id.0')</th>
                                <th class="w-150px text-center">@lang('sales::order.lines.price_ordered.0')</th>
                                <th class="w-150px text-center">@lang('sales::order.lines.quantity_ordered.0')</th>
                                <th class="w-150px text-center">@lang('sales::order.lines.quantity_invoiced.0')</th>
                                <th class="w-150px text-center">@lang('sales::order.lines.total.0')</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($resource->lines as $line)
                                <tr>
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
                                    <td class="align-middle text-right h6">{{ $line->currency->code }} <b>{{ number($line->price_ordered, $line->currency->decimals) }}</b></td>
                                    <td class="align-middle text-center h4 font-weight-bold">{{ $line->quantity_ordered }}</td>
                                    <td class="align-middle text-center h5">{{ $line->quantity_invoiced ?? '--' }}</td>
                                    <td class="align-middle text-right h5 w-100px">{{ $line->currency->code }} <b>{{ number($line->total, $line->currency->decimals) }}</b></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

            </div>
        </div>

        <div class="row">
            <div class="col-2 offset-8 font-weight-bold d-flex align-items-center justify-content-end">Total</div>
            <div class="col-2 text-right">
                <h3 class="pr-1 m-0">{{ $resource->currency->code }} <b>{{ number($resource->total, $resource->currency->decimals) }}</b></h3>
            </div>
        </div>

        @include('backend::components.document-actions', [
            'route'     => 'backend.orders.process',
            'resource'  => $resource,
        ])

    </div>
</div>

@endsection
