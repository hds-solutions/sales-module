<div class="col d-flex mb-1">
    <x-form-foreign name="orders[]"
        :values="$orders"

        show="document_number - transacted_at_pretty"

        {{-- foreign="orders" foreign-add-label="sales::orders.add" --}}
        filtered-by="[name=partnerable_id]" filtered-using="partnerable" data-filtered-keep-id="true"


        label="sales::invoice.order_id.0"
        placeholder="sales::invoice.order_id._"
        {{-- helper="sales::invoice.order_id.?" --}} />

    <button type="button" class="btn btn-danger ml-2"
        data-action="delete">X</button>
</div>
