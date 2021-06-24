@include('backend::components.errors')

<x-backend-form-boolean name="is_purchase"
    :resource="$resource ?? null"

    label="sales::order.is_purchase.0"
    placeholder="sales::order.is_purchase._"
    {{-- helper="sales::order.is_purchase.?" --}} />

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
    :values="$branches" :resource="$resource ?? null"

    foreign="branches" foreign-add-label="sales::branches.add"

    label="sales::order.branch_id.0"
    placeholder="sales::order.branch_id._"
    {{-- helper="sales::order.branch_id.?" --}}>

    <x-backend-form-foreign name="warehouse_id" required secondary
        :values="$branches->pluck('warehouses')->flatten()" :resource="$resource ?? null"

        foreign="warehouses" foreign-add-label="sales::warehouses.add"
        filtered-by="[name=branch_id]" filtered-using="branch"
        append="branch:branch_id"

        label="sales::order.warehouse_id.0"
        placeholder="sales::order.warehouse_id._"
        {{-- helper="sales::order.warehouse_id.?" --}} />

</x-backend-form-foreign>

<x-backend-form-foreign name="employee_id" required
    :values="$employees" :resource="$resource ?? null" show="full_name"

    foreign="employees" foreign-add-label="sales::employees.add"

    label="sales::order.employee_id.0"
    placeholder="sales::order.employee_id._"
    {{-- helper="sales::order.employee_id.?" --}} />

<x-backend-form-foreign name="partnerable_id" required
    :values="$customers" :resource="$resource ?? null" show="business_name"

    foreign="customers" foreign-add-label="sales::customers.add"

    label="sales::order.partnerable_id.0"
    placeholder="sales::order.partnerable_id._"
    {{-- helper="sales::order.partnerable_id.?" --}} />

{{-- TODO: Customer.addresses --}} {{--
<x-backend-form-foreign name="address_id" required
    :values="$customers->pluck('addresses')->flatten()" :resource="$resource ?? null"

    foreign="addresses" foreign-add-label="sales::addresses.add"
    filtered-by="[name=partnerable_id]" filtered-using="customer"
    append="customer:customer_id"

    label="sales::order.address_id.0"
    placeholder="sales::order.address_id._"
    helper="sales::order.address_id.?" /> --}}

<x-backend-form-foreign name="currency_id" :resource="$resource ?? null" required
    :values="backend()->currencies()"

    foreign="currencies" foreign-add-label="cash::currencies.add"
    append="decimals" default="{{ backend()->currency()->id }}"

    label="sales::order.currency_id.0"
    placeholder="sales::order.currency_id._"
    {{-- helper="sales::order.branch_id.?" --}} />
{{--
<div class="form-row form-group mb-0">
    <label class="col-12 col-md-3 col-lg-2 control-label mt-2 mb-3">@lang('sales::order.lines.0')</label>
    <div class="col-12 col-md-9 col-lg-10" data-multiple=".order-line-container" data-template="#new">
        <?php $old_lines = array_group(old('lines') ?? []); ?>
        <!-- add product current lines -->
        @if (isset($resource)) @foreach($resource->lines as $idx => $selected)
            @include('sales::orders.form.line', [
                'products'  => $products,
                'selected'  => $selected,
                'old'       => $old_lines[$idx] ?? null,
            ])
            <?php unset($old_lines[$idx]); ?>
        @endforeach @endif

        <!-- add new added -->
        @foreach($old_lines as $old)
            <!-- ignore empty -->
            @if ( ($old['product_id'] ?? null) === null &&
                ($old['variant_id'] ?? null) === null &&
                ($old['price'] ?? null) === null &&
                ($old['quantity'] ?? null) === null &&
                ($old['total'] ?? null) === null)
                @continue
            @endif
            @include('sales::orders.form.line', [
                'products'  => $products,
                'selected'  => null,
                'old'       => $old,
            ])
        @endforeach

        <!-- add empty for adding new lines -->
        @include('sales::orders.form.line', [
            'products'  => $products,
            'selected'  => null,
            'old'       => null,
        ])
    </div>
</div>
 --}}
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

<x-backend-form-controls
    submit="sales::orders.save"
    cancel="sales::orders.cancel" cancel-route="backend.orders" />
