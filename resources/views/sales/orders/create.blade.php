@extends('sales::layouts.master')

@section('page-name', __('sales::orders.sales.title'))

@section('content')

    <div class="card mb-3">
        <div class="card-header">
            <div class="row">
                <div class="col-4 d-flex align-items-center">
                    <i class="fas fa-company-plus"></i>
                    @lang('sales::orders.sales.create')
                </div>
                <div class="col d-flex justify-content-end">
                    {{-- <a href="{{ route('backend.sales.orders.create') }}"
                        class="btn btn-sm btn-primary">@lang('inventory::orders.create')</a> --}}
                </div>
            </div>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('backend.sales.orders.store') }}" enctype="multipart/form-data">
                @csrf
                @onlyform
                @include('sales::sales.orders.form')
            </form>
        </div>
    </div>

@endsection
