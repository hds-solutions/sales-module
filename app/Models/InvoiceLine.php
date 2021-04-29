<?php

namespace HDSSolutions\Finpar\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Validator;

class InvoiceLine extends X_InvoiceLine {

    public function invoice() {
        return $this->belongsTo(Invoice::class);
    }

    public function currency() {
        return $this->belongsTo(Currency::class);
    }

    public function orderLine() {
        return $this->belongsTo(OrderLine::class);
    }

    public function product() {
        return $this->belongsTo(Product::class);
    }

    public function variant() {
        return $this->belongsTo(Variant::class);
    }

    public function beforeSave(Validator $validator) {
        // copy currency from head if not set
        if (!$this->currency) $this->currency()->associate( $this->invoice->currency );

        // set original price from product|variant
        if (!$this->exists) $this->price_reference =
            // set variant price if variant is set
            $this->variant?->price($this->currency)?->pivot?->price ??
            // otherwise, set product price without variant
            $this->product?->price($this->currency)?->pivot?->price ?? 0;

        // calculate line total amount
        $this->total = $this->price_invoiced * $this->quantity_invoiced;
    }

    public function afterSave() {
        // update invoice total amount
        $this->invoice->update([ 'total' => $this->invoice->lines()->sum('total') ]);
    }

}
