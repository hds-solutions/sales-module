<x-form-input-group class="mb-1">

    <x-form-foreign name="orders[]"
        :values="$orders"

        show="document_number - transacted_at_pretty"

        {{-- foreign="orders" foreign-add-label="sales::orders.add" --}}
        filtered-by="[name=partnerable_id]" filtered-using="partnerable"
        data-filtered-keep-id="true" data-filtered-init="false"

        label="sales::invoice.orders.order_id.0"
        placeholder="sales::invoice.orders.order_id._"
        {{-- helper="sales::invoice.orders.order_id.?" --}} />

    <div class="input-group-append">
        <button type="button" class="btn btn-danger"
            data-action="delete">X</button>
    </div>

</x-form-input-group>
