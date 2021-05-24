@include('backend::components.errors')

<x-backend-form-foreign :resource="$resource ?? null" name="branch_id" required
    foreign="branches" :values="$branches" foreign-add-label="{{ __('sales::branches.add') }}"

    label="{{ __('sales::in_out.branch_id.0') }}"
    placeholder="{{ __('sales::in_out.branch_id._') }}"
    {{-- helper="{{ __('sales::in_out.branch_id.?') }}" --}} />

<x-backend-form-foreign :resource="$resource ?? null" name="warehouse_id" required
    filtered-by="[name=branch_id]" filtered-using="branch"
    foreign="warehouses" :values="$branches->pluck('warehouses')->flatten()" foreign-add-label="{{ __('sales::warehouses.add') }}"

    label="{{ __('sales::in_out.warehouse_id.0') }}"
    placeholder="{{ __('sales::in_out.warehouse_id._') }}"
    {{-- helper="{{ __('sales::product.warehouse_id.?') }}" --}} />

<x-backend-form-foreign :resource="$resource ?? null" name="partnerable_id" required
    show="business_name"
    foreign="customers" :values="$customers" foreign-add-label="{{ __('sales::customers.add') }}"

    label="{{ __('sales::in_out.partnerable_id.0') }}"
    placeholder="{{ __('sales::in_out.partnerable_id._') }}"
    {{-- helper="{{ __('sales::in_out.branch_id.?') }}" --}} />

{{-- TODO ADDRESSES--}}
{{--<x-backend-form-foreign :resource="$resource ?? null" name="warehouse_id" required--}}
{{--                        filtered-by="[name=branch_id]" filtered-using="branch"--}}
{{--                        foreign="warehouses" :values="$branches->pluck('warehouses')->flatten()" foreign-add-label="{{ __('sales::warehouses.add') }}"--}}

{{--                        label="{{ __('sales::in_out.warehouse_id.0') }}"--}}
{{--                        placeholder="{{ __('sales::in_out.warehouse_id._') }}"--}}
{{--    --}}{{-- helper="{{ __('sales::product.warehouse_id.?') }}" --}}{{-- />--}}

<div class="form-row form-group mb-0">
    <label class="col-12 col-md-3 col-lg-2 control-label mt-2 mb-3">@lang('sales::in_out.lines.0')</label>
    <div class="col-12 col-md-9 col-lg-10" data-multiple=".in_out-line-container" data-template="#new">
        @php $old = old('lines') ?? []; @endphp
        {{-- add product current lines --}}
        @if (isset($resource)) @foreach($resource->lines as $idx => $selected)
            @include('sales::in_outs.line', [
                'products'  => $products,
                'selected'  => $selected,
                'old'       => $old[$idx] ?? null,
            ])
            @php unset($old[$idx]); @endphp
        @endforeach @endif

        {{-- add new added --}}
        @foreach($old as $selected)
            @include('sales::in_outs.line', [
                'products'  => $products,
                'selected'  => 0,
                'old'       => $selected,
            ])
        @endforeach

        {{-- add empty for adding new lines --}}
        @include('sales::in_outs.line', [
            'products'  => $products,
            'selected'  => null,
            'old'       => null,
        ])
    </div>
</div>

<x-backend-form-controls
    submit="sales::in_outs.save"
    cancel="sales::in_outs.cancel" cancel-route="backend.in_outs" />
