@extends('backend::layouts.app')

@section('page-name', __('sales::order.title'))

@section('app')

    <div class="card mb-3">
        <div class="card-header">
            <div class="row">
                <div class="col-6">
                    <i class="fas fa-company-plus"></i>
                    @lang('sales::order.create')
                </div>
                <div class="col-6 d-flex justify-content-end">
                    {{-- <a href="{{ route('backend.inventories.create') }}"
                        class="btn btn-sm btn-primary">@lang('inventory::companieies.add')</a> --}}
                </div>
            </div>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('backend.orders.store') }}" enctype="multipart/form-data">
                @csrf
                @onlyform
                @include('sales::tdv.form')
            </form>
        </div>
    </div>

@endsection
