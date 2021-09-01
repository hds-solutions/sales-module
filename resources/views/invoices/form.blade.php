@include('backend::components.errors')

<x-backend-form-boolean name="is_purchase"
    :resource="$resource ?? null"

    label="sales::invoice.is_purchase.0"
    placeholder="sales::invoice.is_purchase._"
    {{-- helper="sales::invoice.is_purchase.?" --}} />

<x-backend-form-foreign name="stamping_id" required
    :values="$stampings" :resource="$resource ?? null"

    foreign="stampings" foreign-add-label="sales::stampings.add"
    show="document_number" append="next:next_document_number"

    label="sales::invoice.stamping_id.0"
    placeholder="sales::invoice.stamping_id._"
    {{-- helper="sales::invoice.stamping_id.?" --}} />

<x-backend-form-text name="document_number" required
    :resource="$resource ?? null"

    data-stamping="[name=stamping_id]"

    label="sales::invoice.document_number.0"
    placeholder="sales::invoice.document_number._"
    {{-- helper="sales::invoice.document_number.?" --}} />

<x-backend-form-datetime name="transacted_at" required
    :resource="$resource ?? null" default="{{ now() }}"

    label="sales::invoice.transacted_at.0"
    placeholder="sales::invoice.transacted_at._"
    {{-- helper="sales::invoice.transacted_at.?" --}} />

<x-backend-form-foreign name="branch_id" required
    :values="$branches" :resource="$resource ?? null"
    :default="backend()->branch()->id"

    foreign="branches" foreign-add-label="sales::branches.add"

    label="sales::invoice.branch_id.0"
    placeholder="sales::invoice.branch_id._"
    {{-- helper="sales::invoice.branch_id.?" --}} />

<x-backend-form-foreign name="employee_id" required
    :values="$employees" :resource="$resource ?? null" show="full_name"

    foreign="employees" foreign-add-label="sales::employees.add"

    label="sales::invoice.employee_id.0"
    placeholder="sales::invoice.employee_id._"
    {{-- helper="sales::invoice.employee_id.?" --}} />

<x-backend-form-foreign name="partnerable_id" required
    :values="$customers" :resource="$resource ?? null" show="business_name"

    foreign="customers" foreign-add-label="sales::customers.add"

    label="sales::invoice.partnerable_id.0"
    placeholder="sales::invoice.partnerable_id._"
    {{-- helper="sales::invoice.partnerable_id.?" --}} />

{{-- TODO: Customer.addresses --}} {{--
<x-backend-form-foreign name="address_id" required
    :values="$customers->pluck('addresses')->flatten()" :resource="$resource ?? null"

    foreign="addresses" foreign-add-label="sales::addresses.add"
    filtered-by="[name=partnerable_id]" filtered-using="customer"
    append="customer:customer_id"

    label="sales::invoice.address_id.0"
    placeholder="sales::invoice.address_id._"
    helper="sales::invoice.address_id.?" /> --}}

<x-backend-form-boolean name="is_credit"
    :resource="$resource ?? null"

    label="sales::invoice.is_credit.0"
    placeholder="sales::invoice.is_credit._"
    {{-- helper="sales::invoice.is_credit.?" --}} />

<x-backend-form-foreign name="currency_id" :resource="$resource ?? null" required
    :values="backend()->currencies()"

    foreign="currencies" foreign-add-label="cash::currencies.add"
    append="decimals" default="{{ backend()->currency()?->id }}"

    label="sales::invoice.currency_id.0"
    placeholder="sales::invoice.currency_id._"
    {{-- helper="sales::invoice.currency_id.?" --}} />

<x-backend-form-multiple name="orders"
    :values="$orders" :selecteds="[]"
    contents-view="sales::invoices.form.orders" row-class="mb-1"

    label="sales::invoice.orders.0" />

<div class="form-row mb-3">
    <div class="offset-2 col-4 d-flex justify-content-end align-items-center">
        <button type="submit" formaction-append="import=true"
            class="btn btn-success">Importar lineas</button>
    </div>
</div>

<x-backend-form-multiple name="lines" values-as="products"
    :values="$products" :selecteds="isset($resource) ? $resource->lines : []" grouped old-filter-fields="product_id,quantity"
    contents-size="xxl" contents-view="sales::invoices.form.line" class="my-2" data-type="invoice"
    card="bg-light"

    label="sales::invoice.lines.0">

    <x-slot name="card-footer">
        <div class="row">
            <div class="col-9 col-xl-10 offset-1">
                <div class="row">
                    <div class="col-3 offset-9">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text font-weight-bold px-3">Total:</span>
                            </div>
                            <input name="total" type="number" min="0" thousand readonly
                                value="{{ old('total', isset($resource) ? number($resource->total, backend()->currencies()->firstWhere('id', $resource->currency_id)->decimals) : 0) }}" tabindex="-1"
                                data-currency-by="[name=currency_id]" data-keep-id="true" data-decimals="0"
                                class="form-control form-control-lg text-right font-weight-bold"
                                placeholder="@lang('sales::invoice.lines.total.0')">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </x-slot>

</x-backend-form-multiple>

<x-backend-form-controls
    submit="sales::invoices.save"
    cancel="sales::invoices.cancel"
        cancel-route="{{ isset($resource)
            ? 'backend.invoices.show:'.$resource->id
            : 'backend.invoices' }}" />
