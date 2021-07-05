<div class="col-10 col-xl-11 d-flex align-items-center">
    <div class="w-100">
        <div class="form-row">

            <div class="col-7">
                <x-form-foreign name="invoices[invoice_id][]" :required="$selected !== null"
                    :values="$invoices" data-live-search="true"
                    default="{{ $old['invoice_id'] ?? $selected?->id }}"

                    filtered-by='[name="partnerable_id"]' filtered-using="partnerable"
                    data-filtered-keep-id="true"

                    show="document_number - transacted_at_pretty - total_pretty" {{-- title="code" --}}
                    append="partnerable:partnerable_id,total,pending-amount:pending_amount"

                    label="sales::receipment.invoices.invoice_id.0"
                    placeholder="sales::receipment.invoices.invoice_id._"
                    {{-- helper="sales::receipment.invoices.invoice_id.?" --}} />
            </div>

            <div class="col-5">
                <div class="input-group">
                    <x-form-amount name="invoices[pending_amount][]" readonly tabindex="-1"
                        data-currency-by="[name=currency_id]" data-keep-id="true"
                        value="{{ $old['pending_amount'] ?? ($selected !== null ? number($selected->pending_amount, currency($selected->currency_id)->decimals) : null) }}"
                        class="text-right"
                        placeholder="sales::receipment.invoices.pending_amount._" />

                    <x-form-amount name="invoices[imputed_amount][]" min="1"
                        :required="$selected !== null"
                        data-currency-by="[name=currency_id]" data-keep-id="true"
                        value="{{ $old['imputed_amount'] ?? ($selected !== null ? number($selected->pivot->imputed_amount, currency($selected->currency_id)->decimals) : null) }}"
                        class="text-right font-weight-bold"
                        placeholder="sales::receipment.invoices.imputed_amount._" />
                </div>
            </div>

        </div>
    </div>
</div>

<div class="col-2 col-xl-1 d-flex justify-content-end align-items-center">
    <button type="button" class="btn btn-danger"
        data-action="delete" tabindex="-1"
        @if ($selected !== null)
        data-confirm="Eliminar Linea?"
        data-text="Esta seguro de eliminar la linea con el producto {{ $selected->imputed_amount }}?"
        data-accept="Si, eliminar"
        @endif>X
    </button>
</div>
