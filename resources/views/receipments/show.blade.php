@extends('backend::layouts.master')

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
                @if (!$resource->isCompleted())
                <a href="{{ route('backend.receipments.edit', $resource) }}"
                    class="btn btn-sm ml-2 btn-info">@lang('sales::receipments.edit')</a>
                @endif
                <a href="{{ route('backend.receipments.create') }}"
                    class="btn btn-sm ml-2 btn-primary">@lang('sales::receipments.create')</a>
            </div>
        </div>
    </div>
    <div class="card-body">

        @include('backend::components.errors')

        <div class="row">
            <div class="col">
                <h2>@lang('sales::receipment.details.0')</h2>
            </div>
        </div>

        <div class="row">
            <div class="col-12">

                <div class="row">
                    <div class="col-4 col-lg-4">@lang('sales::receipment.branch_id.0'):</div>
                    <div class="col-8 col-lg-6 h4">{{ $resource->branch->name }}</div>
                </div>

                <div class="row">
                    <div class="col-4 col-lg-4">@lang('sales::receipment.partnerable_id.0'):</div>
                    <div class="col-8 col-lg-6 h4">{{ $resource->partnerable->fullname }}</div>
                </div>

                <div class="row">
                    <div class="col-4 col-lg-4">@lang('sales::receipment.currency_id.0'):</div>
                    <div class="col-8 col-lg-6 h4">{{ $resource->currency->name }}</div>
                </div>

                {{-- <div class="row">
                    <div class="col-4 col-lg-4">@lang('sales::receipment.description.0'):</div>
                    <div class="col-8 col-lg-6 h4">{{ $resource->description }}</div>
                </div> --}}

                <div class="row">
                    <div class="col-4 col-lg-4">@lang('sales::receipment.transacted_at.0'):</div>
                    <div class="col-8 col-lg-6 h4">{{ pretty_date($resource->transacted_at, true) }}</div>
                </div>

                <div class="row">
                    <div class="col-4 col-lg-4">@lang('sales::receipment.document_status.0'):</div>
                    <div class="col-8 col-lg-6 h4">{{ Document::__($resource->document_status) }}</div>
                </div>

            </div>
        </div>

        <div class="row">
            <div class="col">
                <h2>@lang('sales::receipment.lines.0')</h2>
            </div>
        </div>

        <div class="row">
            <div class="col">

                <div class="table-responsive">
                    <table class="table table-sm table-striped table-borderless table-hover" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th class="w-150px">@lang('sales::receipment.lines.image.0')</th>
                                <th>@lang('sales::receipment.lines.product_id.0')</th>
                                <th>@lang('sales::receipment.lines.variant_id.0')</th>
                                <th class="w-150px text-center">@lang('sales::receipment.lines.price_receipmented.0')</th>
                                <th class="w-150px text-center">@lang('sales::receipment.lines.quantity_receipmented.0')</th>
                                <th class="w-150px text-center">@lang('sales::receipment.lines.total.0')</th>
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
                                    <td class="align-middle pl-3">{{ $line->product->name }}</td>
                                    <td class="align-middle pl-3">
                                        <div>{{ $line->variant->sku ?? '--' }}</div>
                                        @if ($line->variant && $line->variant->values->count())
                                        <div class="small pl-2">
                                            @foreach($line->variant->values as $value)
                                                @if ($value->option_value === null) @continue @endif
                                                <div>{{ $value->option->name }}: <b>{{ $value->option_value->value }}</b></div>
                                            @endforeach
                                        </div>
                                        @endif
                                    </td>
                                    <td class="align-middle text-center h6">{{ $line->currency->code }} <b>{{ number($line->price_receipmented, $line->currency->decimals) }}</b></td>
                                    <td class="align-middle text-center h4 font-weight-bold">{{ $line->quantity_receipmented }}</td>
                                    <td class="align-middle text-center h5 w-100px">{{ $line->currency->code }} <b>{{ number($line->total, $line->currency->decimals) }}</b></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
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
