@extends('backend::layouts.master')

@section('page-name', __('sales::order.title'))

@section('content')

    <div class="card mb-3">
        <div class="card-header">
            <div class="row">
                <div class="col-6">
                    <i class="fas fa-company-plus"></i>
                    @lang('sales::order.edit')
                </div>
                <div class="col-6 d-flex justify-content-end">
                     <a href="{{ route('backend.orders.create') }}"
                        class="btn btn-sm btn-primary">@lang('sales::orders.add')</a>
                </div>
            </div>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('backend.orders.update', $resource) }}" enctype="multipart/form-data">
                @method('PUT')
                @csrf
                @include('sales::orders.form')
            </form>
        </div>
    </div>

@endsection
