@include('backend::components.errors')

<x-backend-form-boolean name="is_purchase"
    :resource="$resource ?? null"

    label="sales::stamping.is_purchase.0"
    placeholder="sales::stamping.is_purchase._"
    {{-- helper="sales::stamping.is_purchase.?" --}} />

<x-backend-form-text :resource="$resource ?? null" name="document_number" required
    label="sales::stamping.document_number.0"
    placeholder="sales::stamping.document_number._"
    {{-- helper="sales::stamping.document_number.?" --}} />

<x-backend-form-date :resource="$resource ?? null" name="valid_from" required
    label="sales::stamping.valid_from.0"
    placeholder="sales::stamping.valid_from._"
    {{-- helper="sales::stamping.valid_from.?" --}} />

<x-backend-form-date :resource="$resource ?? null" name="valid_until" required
    label="sales::stamping.valid_until.0"
    placeholder="sales::stamping.valid_until._"
    {{-- helper="sales::stamping.valid_until.?" --}} />

<div class="mb-3" data-only="is_purchase=true">
    <x-backend-form-foreign name="provider_id"
        :values="$providers" :resource="$resource ?? null"

        foreign="providers" foreign-add-label="sales::providers.add"
        show="full_name"

        label="sales::stamping.provider_id.0"
        placeholder="sales::stamping.provider_id._"
        {{-- helper="sales::stamping.provider_id.?" --}} />
</div>

<x-backend-form-controls
    submit="sales::stampings.save"
    cancel="sales::stampings.cancel" cancel-route="backend.stampings" />
