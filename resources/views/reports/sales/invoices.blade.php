@extends('sales::layouts.master')

@section('page-name', __('sales::reports.sales.invoices.title'))
@section('description', __('sales::reports.sales.invoices.description'))

@section('content')

<div class="card mb-3">
    <div class="card-header">
        <div class="row">
            <div class="col-6 d-flex align-items-center cursor-pointer"
                data-toggle="collapse" data-target="#filters">
                <i class="fas fa-table mr-2"></i>
                @lang('sales::reports.sales.invoices.filters')
            </div>
            <div class="col-6 d-flex justify-content-end" id="report-buttons"></div>
        </div>
        <div class="row collapse @if (request()->has('filters')) show @endif" id="filters">
            <form action="{{ route('backend.reports.sales.invoices') }}"
                class="col mt-2 pt-3 pb-2 border-top">

                <x-backend-form-foreign name="filters[branch]"
                    :values="backend()->branches()" default="{{ request('filters.branch') }}"

                    label="sales::invoice.branch_id.0"
                    placeholder="sales::invoice.branch_id._"
                    {{-- helper="sales::invoice.branch_id.?" --}} />

                <x-backend-form-foreign name="filters[currency]" required
                    :values="backend()->currencies()" default="{{ backend()->currencies()->first()->id }}"

                    label="cash::currency.currency_id.0"
                    placeholder="cash::currency.currency_id._"
                    {{-- helper="cash::currency.currency_id.?" --}} />

                <x-backend-form-foreign name="filters[partnerable]"
                    :values="$customers" show="full_name" default="{{ request('filters.partnerable') }}"
                    data-live-search="true"

                    label="sales::invoice.customer_id.0"
                    placeholder="sales::invoice.customer_id._"
                    {{-- helper="sales::invoice.customer_id.?" --}} />

                <x-backend-form-date name="filters[transacted_at]" range="true"
                    value="{{ now()->subDays(29) }} - {{ now() }}"

                    label="sales::invoice.transacted_at.0"
                    placeholder="sales::invoice.transacted_at._" />

                <button type="submit"
                    class="btn btn-sm btn-outline-primary">Filtrar</button>

                <button type="reset"
                    class="btn btn-sm btn-outline-secondary btn-hover-danger">Limpiar filtros</button>
            </form>
        </div>
    </div>
    <div class="card-body p-0">
        @if ($count)
            <div class="table-responsive">
                {{ $dataTable->table([ 'class' => 'table table-sm table-bordered table-hover table-striped border-0 m-0' ]) }}
            </div>
        @else
            <div class="text-center m-t-30 m-b-30 p-b-10">
                <h2><i class="fas fa-table text-custom"></i></h2>
                <h3>@lang('backend.empty.title')</h3>
                {{-- <p class="text-muted">
                    @lang('backend.empty.description')
                    <a href="{{ route('backend.inventories.create') }}" class="text-custom">
                        <ins>@lang('sales::inventories.create')</ins>
                    </a>
                </p> --}}
            </div>
        @endif
    </div>
</div>

@endsection

@push('config-scripts')
{{ $dataTable->scripts() }}
@endpush
