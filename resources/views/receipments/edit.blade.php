@extends('backend::layouts.master')

@section('page-name', __('sales::receipments.title'))

@section('content')

    <div class="card mb-3">
        <div class="card-header">
            <div class="row">
                <div class="col-6">
                    <i class="fas fa-company-plus"></i>
                    @lang('sales::receipments.edit')
                </div>
                <div class="col-6 d-flex justify-content-end">
                     <a href="{{ route('backend.receipments.create') }}"
                        class="btn btn-sm btn-primary">@lang('sales::receipments.create')</a>
                </div>
            </div>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('backend.receipments.update', $resource) }}" enctype="multipart/form-data">
                @method('PUT')
                @csrf
                @include('sales::receipments.form')
            </form>
        </div>
    </div>

@endsection
