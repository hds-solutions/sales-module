<?php

namespace HDSSolutions\Finpar\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Validator;

class OrderLine extends X_OrderLine {

    public function order() {
        return $this->belongsTo(Order::class);
    }

    public function currency() {
        return $this->belongsTo(Currency::class);
    }

    public function employee() {
        return $this->belongsTo(Employee::class);
    }

    public function product() {
        return $this->belongsTo(Product::class);
    }

    public function variant() {
        return $this->belongsTo(Variant::class);
    }

    public function beforeSave(Validator $validator) {
        // TODO: check if there is drafted Inventories of Variant|Product
        // TODO: check if order already has a line with current Variant|Product
        // TODO: check available stock of Variant|Product

        // copy currency from head if not set
        if (!$this->currency) $this->currency()->associate( $this->order->currency );
        // copy employee from head if not set
        if (!$this->employee) $this->employee()->associate( $this->order->employee );

        // set original price from product|variant
        if (!$this->exists) $this->price_reference =
            // set variant price if variant is set
            $this->variant?->price($this->currency)?->pivot?->price ??
            // otherwise, set product price without variant
            $this->product?->price($this->currency)?->pivot?->price ?? 0;

        // calculate line total amount
        $this->total = $this->price_ordered * $this->quantity_ordered;
    }

    public function afterSave() {
        // update order total amount
        $this->order->update([ 'total' => $this->order->lines()->sum('total') ]);
    }

}
