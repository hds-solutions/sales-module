<?php

namespace HDSSolutions\Finpar\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Validator;

class InvoiceLine extends X_InvoiceLine {

    public function __construct(array|OrderLine $attributes = []) {
        // check if is instance of OrderLine
        if (($orderLine = $attributes) instanceof OrderLine)
            // copy attributes from OrderLine
            $attributes = [
                'currency_id'       => $orderLine->currency_id,
                'order_line_id'     => $orderLine->id,
                'product_id'        => $orderLine->product_id,
                'variant_id'        => $orderLine->variant_id,
                'price_reference'   => $orderLine->price_reference,
                'price_ordered'     => $orderLine->price_ordered,
                'price_invoiced'    => $orderLine->price_ordered,
                'quantity_ordered'  => $orderLine->quantity_ordered,
                'quantity_invoiced' => $orderLine->quantity_ordered,
            ];
        // redirect attributes to parent
        parent::__construct($attributes);
    }

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
