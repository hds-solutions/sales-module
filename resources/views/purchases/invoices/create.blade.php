@extends('sales::layouts.master')

@section('page-name', __('sales::invoices.purchases.title'))

@section('content')

    <div class="card mb-3">
        <div class="card-header">
            <div class="row">
                <div class="col-4 d-flex align-items-center">
                    <i class="fas fa-company-plus"></i>
                    @lang('sales::invoices.purchases.create')
                </div>
                <div class="col d-flex justify-content-end">
                    {{-- <a href="{{ route('backend.purchases.invoices.create') }}"
                        class="btn btn-sm btn-primary">@lang('inventory::invoices.create')</a> --}}
                </div>
            </div>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('backend.purchases.invoices.store') }}" enctype="multipart/form-data">
                @csrf
                @onlyform
                @include('sales::purchases.invoices.form')
            </form>
        </div>
    </div>

@endsection
