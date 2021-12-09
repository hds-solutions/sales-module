@include('backend::components.errors')

<x-backend-form-text name="document_number" required
    :resource="$resource ?? null" :default="$highs['document_number'] ?? null"

    label="sales::order.document_number.0"
    placeholder="sales::order.document_number._"
    {{-- helper="sales::order.document_number.?" --}} />

<x-backend-form-datetime name="transacted_at" required
    :resource="$resource ?? null" default="{{ now() }}"

    label="sales::order.transacted_at.0"
    placeholder="sales::order.transacted_at._"
    {{-- helper="sales::order.transacted_at.?" --}} />

<x-backend-form-foreign name="branch_id" required
    :values="$branches" :resource="$resource ?? null" :default="backend()->branch()?->id"

    foreign="branches" foreign-add-label="backend::branches.add"

    label="sales::order.branch_id.0"
    placeholder="sales::order.branch_id._"
    {{-- helper="sales::order.branch_id.?" --}}>

    <x-backend-form-foreign name="warehouse_id" required secondary
        :values="$branches->pluck('warehouses')->flatten()" :resource="$resource ?? null"
        :default="backend()->warehouse()?->id"

        foreign="warehouses" foreign-add-label="inventory::warehouses.add"
        filtered-by="[name=branch_id]" filtered-using="branch"
        append="branch:branch_id"

        label="sales::order.warehouse_id.0"
        placeholder="sales::order.warehouse_id._"
        {{-- helper="sales::order.warehouse_id.?" --}} />

</x-backend-form-foreign>

<x-backend-form-foreign name="employee_id" required
    :values="$employees" :resource="$resource ?? null" show="full_name"
    :default="backend()->employee()?->id"

    foreign="employees" data-foreign-return="people" foreign-add-label="customers::employees.add"
    data-live-search="true"

    label="sales::order.employee_id.0"
    placeholder="sales::order.employee_id._"
    {{-- helper="sales::order.employee_id.?" --}} />

@yield('partnerable')

<x-backend-form-foreign name="currency_id" :resource="$resource ?? null" required
    :values="backend()->currencies()"

    foreign="currencies" foreign-add-label="cash::currencies.add"
    append="decimals" default="{{ backend()->currency()?->id }}"

    label="sales::order.currency_id.0"
    placeholder="sales::order.currency_id._"
    {{-- helper="sales::order.branch_id.?" --}} />

<x-backend-form-foreign name="price_list_id" :resource="$resource ?? null" required
    :values="$price_lists" default="{{ $price_lists->firstWhere('is_default')?->id }}"

    foreign="price_lists" foreign-add-label="products-catalog::price_lists.add"
    filtered-by="[name='currency_id']" filtered-using="currency" append="currency:currency_id"

    label="sales::order.price_list_id.0"
    placeholder="sales::order.price_list_id._"
    {{-- helper="sales::order.branch_id.?" --}} />

<x-backend-form-multiple name="lines" values-as="products"
    :values="$products" :selecteds="isset($resource) ? $resource->lines : []" grouped old-filter-fields="product_id,quantity"
    contents-size="xxl" contents-view="sales::orders.form.line" class="my-2" data-type="order"
    card="bg-light"

    label="sales::order.lines.0">

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
                                value="{{ old('total', isset($resource) ? number($resource->total, $resource->currency->decimals) : null) }}" tabindex="-1"
                                data-currency-by="[name=currency_id]" data-keep-id="true" data-decimals="0"
                                class="form-control form-control-lg text-right font-weight-bold"
                                placeholder="@lang('sales::order.lines.total.0')">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </x-slot>

</x-backend-form-multiple>

@yield('buttons')
