<div class="col-1 d-flex align-items-center justify-content-center">
    <div class="position-relative d-flex align-items-center h-50px">
        <img src="" class="img-fluid mh-50px" id="line_preview">
    </div>
    <x-form-input type="text" name="product-finder" placeholder="sales::order.lines.product_id.0" />
</div>

<div class="col-9 col-xl-10 d-flex align-items-center">
    <div class="w-100">
        <div class="form-row">

            <div class="col-4">
                <x-form-foreign name="lines[product_id][]" :required="$selected !== null"
                    :values="$products" data-live-search="true"
                    default="{{ $old['product_id'] ?? $selected?->product_id }}"

                    {{-- show="code name" title="code" --}}
                    append="url:images.0.url??backend-module/assets/images/default.jpg"
                    data-preview="#line_preview" data-preview-init="false"
                    data-preview-url-prepend="{{ asset('') }}"

                    foreign="products" foreign-add-label="products-catalog::products.add"

                    label="sales::invoice.lines.product_id.0"
                    placeholder="sales::invoice.lines.product_id._"
                    {{-- helper="sales::invoice.lines.product_id.?" --}} />
            </div>

            <div class="col-4">
                <x-form-foreign name="lines[variant_id][]" {{-- :required="$selected !== null" --}}
                    :values="$products->pluck('variants')->flatten()" data-live-search="true"
                    default="{{ $old['variant_id'] ?? $selected?->variant_id }}"

                    filtered-by='[name="lines[product_id][]"]' filtered-using="product"
                    data-filtered-init="false"

                    show="sku" {{-- title="code" --}}
                    {{-- append="url:images.0.url??backend-module/assets/images/default.jpg" --}}
                    {{-- data-preview="#line_preview" data-preview-init="false" --}}
                    {{-- data-preview-url-prepend="{{ asset('') }}" --}}

                    foreign="variants" foreign-add-label="products-catalog::variants.add"

                    {{-- label="sales::invoice.lines.variant_id.0" --}}
                    placeholder="sales::invoice.lines.variant_id._"
                    {{-- helper="sales::invoice.lines.variant_id.?" --}} />
            </div>

            <div class="col-4">
                <div class="input-group">
                    <x-form-amount name="lines[price][]" min="1"
                        :required="$selected !== null"
                        data-currency-by="[name='currency_id']" data-keep-id="true"
                        value="{{ $old['price'] ?? ($selected !== null ? number($selected->price_invoiced, currency($selected->currency_id)->decimals) : null) }}"
                        class="text-right"
                        placeholder="sales::invoice.lines.price_invoiced._" />

                    <x-form-input type="number" name="lines[quantity][]" min="1" max="{{ $selected?->orderLines->count() ? $selected?->orderLines->sum('pivot.quantity_ordered') : null }}"
                        :required="$selected !== null"
                        value="{{ $old['quantity'] ?? $selected?->quantity_invoiced }}"
                        class="text-center"
                        placeholder="sales::invoice.lines.quantity_invoiced._" />

                    <x-form-amount name="lines[total][]" min="1" readonly tabindex="-1"
                        data-currency-by="[name='currency_id']" data-keep-id="true"
                        value="{{ $old['total'] ?? ($selected !== null ? number($selected->total, currency($selected->currency_id)->decimals) : null) }}"
                        class="text-right font-weight-bold"
                        placeholder="sales::invoice.lines.total._" />
                </div>
            </div>

        </div>
    </div>
</div>

<div class="col-2 col-xl-1 d-flex justify-content-end align-items-center">
    <button type="button" class="btn btn-danger" tabindex="-1"
        data-action="delete"
        @if ($selected !== null)
        data-confirm="Eliminar Linea?"
        data-text="Esta seguro de eliminar la linea con el producto {{ $selected->product->name }}?"
        data-accept="Si, eliminar"
        @endif>X
    </button>
</div>
