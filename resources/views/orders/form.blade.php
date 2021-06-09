@include('backend::components.errors')

<x-backend-form-foreign :resource="$resource ?? null" name="partnerable_id" required
    show="business_name"
    foreign="customers" :values="$customers" foreign-add-label="{{ __('sales::customers.add') }}"

    label="{{ __('sales::order.partnerable_id.0') }}"
    placeholder="{{ __('sales::order.partnerable_id._') }}"
    {{-- helper="{{ __('sales::inventory.branch_id.?') }}" --}} />

{{-- TODO ADDRESSES--}}
{{--<x-backend-form-foreign :resource="$resource ?? null" name="warehouse_id" required--}}
{{--                        filtered-by="[name=branch_id]" filtered-using="branch"--}}
{{--                        foreign="warehouses" :values="$branches->pluck('warehouses')->flatten()" foreign-add-label="{{ __('sales::warehouses.add') }}"--}}

{{--                        label="{{ __('sales::inventory.warehouse_id.0') }}"--}}
{{--                        placeholder="{{ __('sales::inventory.warehouse_id._') }}"--}}
{{--    --}}{{-- helper="{{ __('sales::product.warehouse_id.?') }}" --}}{{-- />--}}

<x-backend-form-foreign :resource="$resource ?? null" name="currency_id" required
    foreign="currencies" :values="backend()->currencies()" foreign-add-label="{{ __('sales::currencies.add') }}"
    append="decimals" default="{{ backend()->currency()->id }}"

    label="{{ __('sales::order.currency_id.0') }}"
    placeholder="{{ __('sales::order.currency_id._') }}"
    {{-- helper="{{ __('sales::inventory.branch_id.?') }}" --}} />
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
                                value="{{ old('total') }}" tabindex="-1"
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
