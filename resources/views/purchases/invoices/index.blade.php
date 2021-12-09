@extends('sales::layouts.master')

@section('page-name', __('sales::invoices.purchases.title'))
@section('description', __('sales::invoices.purchases.description'))

@section('content')

    <div class="card mb-3">
        <div class="card-header">
            <div class="row">
                <div class="col-6 d-flex align-items-center cursor-pointer"
                    data-toggle="collapse" data-target="#filters">
                    <i class="fas fa-table mr-2"></i>
                    @lang('sales::invoices.purchases.index')
                </div>
                <div class="col-6 d-flex justify-content-end">
                    <a href="{{ route('backend.purchases.invoices.create') }}"
                       class="btn btn-sm btn-outline-primary">@lang('sales::invoices.purchases.create')</a>
                </div>
            </div>
            <div class="row collapse @if (request()->has('filters')) show @endif" id="filters">
                <form action="{{ route('backend.purchases.invoices') }}"
                    class="col mt-2 pt-3 pb-2 border-top">

                    <x-backend-form-foreign name="filters[partnerable]"
                        :values="$providers" show="business_name" default="{{ request('filters.partnerable') }}"

                        label="sales::invoice.provider_id.0"
                        placeholder="sales::invoice.provider_id._"
                        {{-- helper="sales::invoice.provider_id.?" --}} />

                    <x-backend-form-foreign name="filters[currency]"
                        :values="backend()->currencies()" default="{{ request('filters.currency') }}"

                        label="sales::invoice.currency_id.0"
                        placeholder="sales::invoice.currency_id._"
                        {{-- helper="sales::invoice.branch_id.?" --}} />

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
                        'resource'  => 'invoices',
                        'actions'   => [ 'show', 'update', 'delete' ],
                        'label'     => '{resource.document_number}',
                    ])
                </div>
            @else
                <div class="text-center m-t-30 m-b-30 p-b-10">
                    <h2><i class="fas fa-table text-custom"></i></h2>
                    <h3>@lang('backend.empty.title')</h3>
                    <p class="text-muted">
                        @lang('backend.empty.description')
                        <a href="{{ route('backend.purchases.invoices.create') }}" class="text-custom">
                            <ins>@lang('sales::invoices.purchases.create')</ins>
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
