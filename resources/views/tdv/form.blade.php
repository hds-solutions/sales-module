@include('backend::components.errors')

<x-backend-form-foreign :resource="$resource ?? null" name="customer_id" required field="business_name"
                        foreign="customers" :values="$customers" foreign-add-label="{{ __('sales::customers.add') }}"

                        label="{{ __('sales::order.customer_id.0') }}"
                        placeholder="{{ __('sales::order.customer_id._') }}"
    {{-- helper="{{ __('sales::inventory.branch_id.?') }}" --}} />

{{-- TODO ADDRESSES--}}
{{--<x-backend-form-foreign :resource="$resource ?? null" name="warehouse_id" required--}}
{{--                        filtered-by="[name=branch_id]" filtered-using="branch"--}}
{{--                        foreign="warehouses" :values="$branches->pluck('warehouses')->flatten()" foreign-add-label="{{ __('sales::warehouses.add') }}"--}}

{{--                        label="{{ __('sales::inventory.warehouse_id.0') }}"--}}
{{--                        placeholder="{{ __('sales::inventory.warehouse_id._') }}"--}}
{{--    --}}{{-- helper="{{ __('sales::product.warehouse_id.?') }}" --}}{{-- />--}}

<x-backend-form-foreign :resource="$resource ?? null" name="currency_id" required
                        foreign="currencies" :values="$currencies" foreign-add-label="{{ __('sales::currencies.add') }}"

                        label="{{ __('sales::order.currency_id.0') }}"
                        placeholder="{{ __('sales::order.currency_id._') }}"
    {{-- helper="{{ __('sales::inventory.branch_id.?') }}" --}} />

<x-backend-form-foreign :resource="$resource ?? null" name="branch_id" required
                        foreign="branches" :values="$branches" foreign-add-label="{{ __('inventory::branches.add') }}"

                        label="{{ __('inventory::inventory.branch_id.0') }}"
                        placeholder="{{ __('inventory::inventory.branch_id._') }}"
    {{-- helper="{{ __('inventory::inventory.branch_id.?') }}" --}} />

<x-backend-form-foreign :resource="$resource ?? null" name="warehouse_id" required
                        filtered-by="[name=branch_id]" filtered-using="branch"
                        foreign="warehouses" :values="$branches->pluck('warehouses')->flatten()" foreign-add-label="{{ __('inventory::warehouses.add') }}"

                        label="{{ __('inventory::inventory.warehouse_id.0') }}"
                        placeholder="{{ __('inventory::inventory.warehouse_id._') }}"
    {{-- helper="{{ __('inventory::product.warehouse_id.?') }}" --}} />

<div class="form-row form-group mb-0">
    <label class="col-12 col-md-3 control-label mt-2 mb-3">@lang('sales::inventory.lines.0')</label>
    <div class="col-9" data-multiple=".order-line-container" data-template="#new">
        @php $old = old('lines') ?? []; @endphp
        {{-- add product current lines --}}
        @if (isset($resource)) @foreach($resource->lines as $idx => $selected)
            @include('sales::orders.line', [
                'products'  => $products,
                'selected'  => $selected,
                'old'       => $old[$idx] ?? null,
            ])
            @php unset($old[$idx]); @endphp
        @endforeach @endif

        {{-- add new added --}}
        @foreach($old as $selected)
            @include('sales::orders.line', [
                'products'  => $products,
                'selected'  => 0,
                'old'       => $selected,
            ])
        @endforeach

        {{-- add empty for adding new lines --}}
        @include('sales::orders.line', [
            'products'  => $products,
            'selected'  => null,
            'old'       => null,
        ])
    </div>
</div>

<x-backend-form-controls
    submit="sales::inventories.save"
    cancel="sales::inventories.cancel" cancel-route="backend.inventories" />
