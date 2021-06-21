@extends('sales::layouts.master')

@section('page-name', __('sales::invoices.title'))

@section('content')

    <div class="card mb-3">
        <div class="card-header">
            <div class="row">
                <div class="col-6">
                    <i class="fas fa-company-plus"></i>
                    @lang('sales::invoices.edit')
                </div>
                <div class="col-6 d-flex justify-content-end">
                     <a href="{{ route('backend.invoices.create') }}"
                        class="btn btn-sm btn-primary">@lang('sales::invoices.create')</a>
                </div>
            </div>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('backend.invoices.update', $resource) }}" enctype="multipart/form-data">
                @method('PUT')
                @csrf
                @include('sales::invoices.form')
            </form>
        </div>
    </div>

@endsection
