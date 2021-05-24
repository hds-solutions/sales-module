@include('backend::components.errors')

<x-backend-form-foreign :resource="$resource ?? null" name="partnerable_id" required
    show="business_name"
    foreign="customers" :values="$customers" foreign-add-label="{{ __('sales::customers.add') }}"

    label="{{ __('sales::receipment.partnerable_id.0') }}"
    placeholder="{{ __('sales::receipment.partnerable_id._') }}"
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

    label="{{ __('sales::receipment.currency_id.0') }}"
    placeholder="{{ __('sales::receipment.currency_id._') }}"
    {{-- helper="{{ __('sales::inventory.branch_id.?') }}" --}} />

<div class="form-row form-group mb-0">
    <label class="col-12 col-md-3 col-lg-2 control-label mt-2 mb-3">@lang('sales::receipment.lines.0')</label>
    <div class="col-12 col-md-9 col-lg-10" data-multiple=".receipment-line-container" data-template="#new">
        @php $old = old('lines') ?? []; @endphp
        {{-- add product current lines --}}
        @if (isset($resource)) @foreach($resource->lines as $idx => $selected)
            @include('sales::receipments.line', [
                'products'  => $products,
                'selected'  => $selected,
                'old'       => $old[$idx] ?? null,
            ])
            @php unset($old[$idx]); @endphp
        @endforeach @endif

        {{-- add new added --}}
        @foreach($old as $selected)
            @include('sales::receipments.line', [
                'products'  => $products,
                'selected'  => 0,
                'old'       => $selected,
            ])
        @endforeach

        {{-- add empty for adding new lines --}}
        @include('sales::receipments.line', [
            'products'  => $products,
            'selected'  => null,
            'old'       => null,
        ])
    </div>
</div>

<x-backend-form-controls
    submit="sales::receipments.save"
    cancel="sales::receipments.cancel" cancel-route="backend.receipments" />
