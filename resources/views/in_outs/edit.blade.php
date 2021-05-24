@extends('backend::layouts.master')

@section('page-name', __('sales::in_outs.title'))

@section('content')

    <div class="card mb-3">
        <div class="card-header">
            <div class="row">
                <div class="col-6">
                    <i class="fas fa-company-plus"></i>
                    @lang('sales::in_outs.edit')
                </div>
                <div class="col-6 d-flex justify-content-end">
                     <a href="{{ route('backend.in_outs.create') }}"
                        class="btn btn-sm btn-primary">@lang('sales::in_outs.create')</a>
                </div>
            </div>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('backend.in_outs.update', $resource) }}" enctype="multipart/form-data">
                @method('PUT')
                @csrf
                @include('sales::in_outs.form')
            </form>
        </div>
    </div>

@endsection
