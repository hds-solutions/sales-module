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

<div class="mb-3" data-only="is_purchase=false">

    <x-backend-form-number :resource="$resource ?? null" name="length" min="3" :required="isset($resource) && !$resource->is_purchase"
        label="sales::stamping.length.0"
        placeholder="sales::stamping.length._"
        {{-- helper="sales::stamping.length.?" --}} />

    <x-backend-form-number :resource="$resource ?? null" name="start" min="1" :required="isset($resource) && !$resource->is_purchase"
        label="sales::stamping.start.0"
        placeholder="sales::stamping.start._"
        {{-- helper="sales::stamping.start.?" --}} />

    <x-backend-form-number :resource="$resource ?? null" name="end" min="1" :required="isset($resource) && !$resource->is_purchase"
        label="sales::stamping.end.0"
        placeholder="sales::stamping.end._"
        {{-- helper="sales::stamping.end.?" --}} />

    <x-backend-form-number :resource="$resource ?? null" name="current"
        :min="isset($resource) ? $resource->current : 1"
        label="sales::stamping.current.0"
        placeholder="sales::stamping.current._"
        {{-- helper="sales::stamping.current.?" --}} />
</div>

<x-backend-form-controls
    submit="sales::stampings.save"
    cancel="sales::stampings.cancel" cancel-route="backend.stampings" />
