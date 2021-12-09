@extends('sales::invoices.form')

@section('partnerable')
    <x-backend-form-foreign name="partnerable_id" required
        :values="$providers" :resource="$resource ?? null"

        show="business_name" subtext="ftid" data-show-subtext="true"

        foreign="providers" data-foreign-return="people" foreign-add-label="customers::providers.add"
        data-live-search="true"

        label="sales::order.provider_id.0"
        placeholder="sales::order.provider_id._"
        {{-- helper="sales::order.provider_id.?" --}} />

    {{-- TODO: Customer.addresses --}} {{--
    <x-backend-form-foreign name="address_id" required
        :values="$providers->pluck('addresses')->flatten()" :resource="$resource ?? null"

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
            ? 'backend.purchases.invoices.show:'.$resource->id
            : 'backend.purchases.invoices' }}" />
@endsection
