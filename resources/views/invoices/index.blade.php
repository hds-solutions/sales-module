@extends('sales::layouts.master')

@section('page-name', __('sales::invoices.title'))
@section('description', __('sales::invoices.description'))

@section('content')

    <div class="card mb-3">
        <div class="card-header">
            <div class="row">
                <div class="col-6">
                    <i class="fas fa-table"></i>
                    @lang('sales::invoices.index')
                </div>
                <div class="col-6 d-flex justify-content-end">
                    <a href="{{ route('backend.invoices.create') }}"
                       class="btn btn-sm btn-primary">@lang('sales::invoices.create')</a>
                </div>
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
                    <h3>@lang('sales::invoices.title')</h3>
                    <p class="text-muted">
                        @lang('sales::invoices.description')
                        <a href="{{ route('backend.invoices.create') }}" class="text-custom">
                            <ins>@lang('sales::invoices.create')</ins>
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
