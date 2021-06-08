<div class="form-row mb-3 order-line-container" @if ($selected === null && $old === null) id="new" @else data-used="true" @endif>
    <div class="col-12">
        <div class="card bg-light">
            <div class="card-body py-2">
                <div class="form-row">
                    <div class="col-1 d-flex justify-content-center">
                        <div class="position-relative d-flex align-items-center">
                            <img src="" class="img-fluid mh-50px" id="line_preview">
                        </div>
                    </div>
                    <div class="col-9 col-xl-10 d-flex align-items-center">
                        <div class="form-row">
                            <div class="col-4">
{{--
                                <select name="lines[product_id][]" data-live-search="true"
                                    @if ($selected !== null) required @endif
                                    data-preview="#line_preview" data-preview-init="false"
                                    value="{{ isset($selected) && !old('product_id') ? $selected->product_id : old('product_id') }}"
                                    class="form-control selectpicker {{ $errors->has('product_id') ? 'is-danger' : '' }}"
                                    placeholder="@lang('sales::order.lines.product_id._')">

                                    <option value="" selected disabled
                                            hidden>@lang('sales::order.lines.product_id.0')</option>

                                    @foreach($products as $product)
                                        <option value="{{ $product->id }}"
                                            url="{{ asset($product->images->first()->url ?? 'backend-module/assets/images/default.jpg') }}"
                                            @if (isset($selected) && !old('product_id') && $selected->product_id == $product->id ||
                                                old('product_id') == $product->id) selected @endif>{{ $product->name }}</option>
                                    @endforeach
                                </select>
 --}}
                                <x-form-foreign name="lines[product_id][]" id="f{{ $id = Str::random(16) }}"
                                    :values="$products" data-live-search="true"
                                    default="{{ $old['product_id'] ?? $selected?->id }}"
                                    :required="$selected !== null"

                                    {{-- show="code name" title="code" --}}
                                    {{-- append="decimals" --}}

                                    {{-- foreign="" --}}
                                    {{-- foreign-add-label="products-catalog::currencies.add" --}}

                                    label="sales::order.lines.product_id.0"
                                    placeholder="sales::order.lines.product_id._"
                                    {{-- helper="sales::order.lines.product_id.?" --}}
                                    />
                            </div>
                            <div class="col-4">
                                <select name="lines[variant_id][]"
                                    data-filtered-by='[name="lines[product_id][]"]' data-filtered-using="product"
                                    data-filtered-init="false"
                                    value="{{ isset($selected) && !old('variant_id') ? $selected->variant_id : old('variant_id') }}"
                                    class="form-control selectpicker {{ $errors->has('variant_id') ? 'is-danger' : '' }}"
                                    placeholder="@lang('sales::order.lines.variant_id._')">

                                    <option value="" selected disabled
                                            hidden>@lang('sales::order.lines.variant_id.0')</option>

                                    @foreach($products->pluck('variants')->flatten() as $variant)
                                        <option value="{{ $variant->id }}" data-product="{{ $variant->product_id }}"
                                            @if (isset($selected) && !old('variant_id') && $selected->variant_id == $variant->id ||
                                                old('variant_id') == $variant->id) selected @endif>{{ $variant->sku }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-4">
                                <div class="input-group">
{{--
                                    <input name="lines[price][]" type="number" min="0" thousand
                                       value="{{ $selected->price ?? '' }}" data-decimals="{{ isset($resource) ? $resource->currency->decimals : 0 }}"
                                       class="form-control text-right"
                                       placeholder="@lang('sales::order.lines.price._')">
 --}}
                                    <x-form-amount name="lines[price][]" min="1"
                                        :required="$selected !== null"
                                        {{-- value="{{ $old['price'] ?? ($selected !== null ? number($selected->pivot->cost, $selected->pivot->currency->decimals) : null) }}" --}}
                                        {{-- data-currency-by="[name='prices[currency_id][]']" --}}
                                        placeholder="sales::order.lines.price._" />
                                    <input name="lines[quantity][]" type="number" min="1"
                                       value="{{ $selected->quantity ?? '' }}"
                                       class="form-control text-center"
                                       placeholder="@lang('sales::order.lines.quantity.0')">
                                    <input name="lines[total][]" type="number" min="0" thousand readonly
                                       value="{{ $selected->total ?? '' }}" data-decimals="{{ isset($resource) ? $resource->currency->decimals : 0 }}"
                                       class="form-control text-right"
                                       placeholder="@lang('sales::order.lines.total.0')">
                                </div>
                            </div>

                        </div>
                    </div>
                    <div class="col-2 col-xl-1 d-flex justify-content-end align-items-center">
                        <button type="button" class="btn btn-danger"
                            data-action="delete"
                            @if ($selected !== null)
                            data-confirm="Eliminar Linea?"
                            data-text="Esta seguro de eliminar la linea con el producto {{ $selected->product->name }}?"
                            data-accept="Si, eliminar"
                            @endif>X
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
