@extends('backend::layouts.master')

@section('page-name', __('sales::stampings.title'))

@section('content')

<div class="card mb-3">
    <div class="card-header">
        <div class="row">
            <div class="col-6 d-flex align-items-center">
                <i class="fas fa-stamping-plus"></i>
                @lang('sales::stampings.edit')
            </div>
            <div class="col-6 d-flex justify-content-end">
                <a href="{{ route('backend.stampings.create') }}"
                    class="btn btn-sm btn-outline-primary">@lang('sales::stampings.create')</a>
            </div>
        </div>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('backend.stampings.update', $resource) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            @include('sales::stampings.form')
        </form>
    </div>
</div>

@endsection
