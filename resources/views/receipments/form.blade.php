@include('backend::components.errors')

<x-backend-form-boolean name="is_purchase"
    :resource="$resource ?? null"

    label="sales::receipment.is_purchase.0"
    placeholder="sales::receipment.is_purchase._"
    {{-- helper="sales::receipment.is_purchase.?" --}} />

<x-backend-form-text name="document_number" required
    :resource="$resource ?? null" :default="$highs['document_number'] ?? null"

    label="sales::receipment.document_number.0"
    placeholder="sales::receipment.document_number._"
    {{-- helper="sales::receipment.document_number.?" --}} />

<x-backend-form-datetime name="transacted_at" required
    :resource="$resource ?? null" default="{{ now() }}"

    label="sales::receipment.transacted_at.0"
    placeholder="sales::receipment.transacted_at._"
    {{-- helper="sales::receipment.transacted_at.?" --}} />

<x-backend-form-foreign name="employee_id" required
    :values="$employees" :resource="$resource ?? null" show="full_name"

    foreign="employees" foreign-add-label="sales::employees.add"

    label="sales::receipment.employee_id.0"
    placeholder="sales::receipment.employee_id._"
    {{-- helper="sales::receipment.employee_id.?" --}} />

<x-backend-form-foreign name="partnerable_id" required
    :values="$customers" :resource="$resource ?? null" show="business_name"

    foreign="customers" foreign-add-label="sales::customers.add"

    label="sales::receipment.partnerable_id.0"
    placeholder="sales::receipment.partnerable_id._"
    {{-- helper="sales::receipment.partnerable_id.?" --}} />

{{-- TODO: Customer.addresses --}} {{--
<x-backend-form-foreign name="address_id" required
    :values="$customers->pluck('addresses')->flatten()" :resource="$resource ?? null"

    foreign="addresses" foreign-add-label="sales::addresses.add"
    filtered-by="[name=partnerable_id]" filtered-using="customer"
    append="customer:customer_id"

    label="sales::receipment.address_id.0"
    placeholder="sales::receipment.address_id._"
    helper="sales::receipment.address_id.?" /> --}}

<x-backend-form-foreign name="currency_id" :resource="$resource ?? null" required
    :values="backend()->currencies()"

    foreign="currencies" foreign-add-label="cash::currencies.add"
    append="decimals" default="{{ backend()->currency()->id }}"

    label="sales::receipment.currency_id.0"
    placeholder="sales::receipment.currency_id._"
    {{-- helper="sales::receipment.currency_id.?" --}} />

<x-backend-form-multiple name="invoices" :values="$invoices"
    :selecteds="isset($resource) ? $resource->invoices : []" grouped old-filter-fields="invoice_id,imputed_amount"
    contents-size="xxl" contents-view="sales::receipments.form.invoice" data-type="receipment"
    class="my-2 px-1" container-class="my-1" card

    label="sales::receipment.invoices.0">

    <x-slot name="card-footer">
        <div class="row">
            <div class="col-9 col-xl-10 offset-1">
                <div class="row">
                    <div class="col-4 offset-8 px-1">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text font-weight-bold px-3">@lang('sales::receipment.invoices.invoices_amount.0'):</span>
                            </div>
                            <x-form-amount name="invoices_amount" readonly tabindex="-1"
                                data-currency-by="[name=currency_id]" data-keep-id="true"
                                value="{{ old('invoices_amount', isset($resource) ? number($resource->invoices_amount, backend()->currencies()->firstWhere('id', $resource->currency_id)->decimals) : null) }}"
                                class="text-right font-weight-bold"
                                placeholder="sales::receipment.invoices.invoices_amount._" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </x-slot>

</x-backend-form-multiple>

<x-backend-form-multiple name="payments" :values="$customers->pluck('creditNotes')->flatten()" values-as="creditNotes"
    :extra="isset($resource) ? $resource->payments : []" extra-as="payments"
    :selecteds="isset($resource) ? $resource->payments : []" grouped old-filter-fields="payment_type,payment_amount"
    contents-size="xxl" contents-view="sales::receipments.form.payment" data-type="receipment"
    class="my-2 px-1" container-class="my-1" card

    label="sales::receipment.payments.0">

    <x-slot name="card-footer">
        <div class="row">
            <div class="col-9 col-xl-10 offset-1">
                <div class="row">
                    <div class="col-4 offset-8 px-1">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text font-weight-bold px-3">@lang('sales::receipment.payments.payments_amount.0'):</span>
                            </div>
                            <x-form-amount name="payments_amount" readonly tabindex="-1"
                                data-currency-by="[name=currency_id]" data-keep-id="true"
                                value="{{ old('payments_amount', isset($resource) ? number($resource->payments_amount, backend()->currencies()->firstWhere('id', $resource->currency_id)->decimals) : null) }}"
                                class="text-right font-weight-bold"
                                placeholder="sales::receipment.payments.payments_amount._" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </x-slot>

</x-backend-form-multiple>

<x-backend-form-controls
    submit="sales::receipments.save"
    cancel="sales::receipments.cancel"
    cancel-route="{{ isset($resource)
        ? 'backend.receipments.show:'.$resource->id
        : 'backend.receipments' }}" />
