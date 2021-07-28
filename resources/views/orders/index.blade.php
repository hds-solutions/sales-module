@extends('sales::layouts.master')

@section('page-name', __('sales::orders.title'))
@section('description', __('sales::orders.description'))

@section('content')

    <div class="card mb-3">
        <div class="card-header">
            <div class="row">
                <div class="col-6 d-flex align-items-center cursor-pointer"
                    data-toggle="collapse" data-target="#filters">
                    <i class="fas fa-table mr-2"></i>
                    @lang('sales::orders.index')
                </div>
                <div class="col-6 d-flex justify-content-end">
                    <a href="{{ route('backend.orders.create') }}"
                       class="btn btn-sm btn-outline-primary">@lang('sales::orders.create')</a>
                </div>
            </div>
            <div class="row collapse @if (request()->has('filters')) show @endif" id="filters">
                <form action="{{ route('backend.orders') }}"
                    class="col mt-2 pt-3 pb-2 border-top">

                    <x-backend-form-foreign name="filters[partnerable]"
                        :values="$customers" show="business_name" default="{{ request('filters.partnerable') }}"

                        label="sales::order.partnerable_id.0"
                        placeholder="sales::order.partnerable_id._"
                        {{-- helper="sales::order.partnerable_id.?" --}} />

                    <x-backend-form-foreign name="filters[currency]"
                        :values="backend()->currencies()" default="{{ request('filters.currency') }}"

                        label="sales::order.currency_id.0"
                        placeholder="sales::order.currency_id._"
                        {{-- helper="sales::order.branch_id.?" --}} />

                    <button type="submit"
                        class="btn btn-sm btn-primary">Filtrar</button>

                    <button type="reset"
                        class="btn btn-sm btn-secondary btn-hover-danger">Limpiar filtros</button>

                </form>
            </div>
        </div>
        <div class="card-body">
            @if ($count)
                <div class="table-responsive">
                    {{ $dataTable->table() }}
                    @include('backend::components.datatable-actions', [
                        'actions'   => [ 'show', 'update', 'delete' ],
                        'label'     => '{resource.name}',
                    ])
                </div>
            @else
                <div class="text-center m-t-30 m-b-30 p-b-10">
                    <h2><i class="fas fa-table text-custom"></i></h2>
                    <h3>@lang('sales::orders.title')</h3>
                    <p class="text-muted">
                        @lang('sales::orders.description')
                        <a href="{{ route('backend.orders.create') }}" class="text-custom">
                            <ins>@lang('sales::orders.create')</ins>
                        </a>
                    </p>
                </div>
            @endif
        </div>
    </div>

@endsection

@push('config-scripts')
    {{ $dataTable->scripts() }}
@endpush
