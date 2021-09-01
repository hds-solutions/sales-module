@include('backend::components.errors')

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

<x-backend-form-controls
    submit="sales::stampings.save"
    cancel="sales::stampings.cancel" cancel-route="backend.stampings" />
