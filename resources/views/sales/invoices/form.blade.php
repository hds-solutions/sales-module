@extends('sales::invoices.form')

@section('partnerable')
    <x-backend-form-foreign name="partnerable_id" required
        :values="$customers" :resource="$resource ?? null"

        show="business_name" subtext="ftid" data-show-subtext="true"

        foreign="customers" data-foreign-return="people" foreign-add-label="customers::customers.add"
        data-live-search="true"

        label="sales::order.customer_id.0"
        placeholder="sales::order.customer_id._"
        {{-- helper="sales::order.customer_id.?" --}} />

    {{-- TODO: Customer.addresses --}} {{--
    <x-backend-form-foreign name="address_id" required
        :values="$customers->pluck('addresses')->flatten()" :resource="$resource ?? null"

        foreign="addresses" foreign-add-label="sales::addresses.add"
        filtered-by="[name=partnerable_id]" filtered-using="customer"
        append="customer:customer_id"

        label="sales::order.address_id.0"
        placeholder="sales::order.address_id._"
        helper="sales::order.address_id.?" /> --}}
@endsection

@section('buttons')
    <x-backend-form-controls
        submit="sales::invoices.save"
        cancel="sales::invoices.cancel" cancel-route="{{ isset($resource)
            ? 'backend.sales.invoices.show:'.$resource->id
            : 'backend.sales.invoices' }}" />
@endsection
