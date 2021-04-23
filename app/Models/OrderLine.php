<?php

namespace HDSSolutions\Finpar\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Validator;

class OrderLine extends X_OrderLine {

    public function order() {
        return $this->belongsTo(Order::class);
    }

    public function product() {
        return $this->belongsTo(Product::class);
    }

    public function variant() {
        return $this->belongsTo(Variant::class);
    }

    public function currency() {
        return $this->belongsTo(Currency::class);
    }

    public function beforeSave(Validator $validator) {
        // set original price from product
        $this->original_price = $this->variant?->price($this->order->currency)?->pivot?->price ?? $this->product?->price($this->order->currency)?->pivot?->price;
        // calculate line total amount
        $this->total = $this->price * $this->quantity;
    }

    public function afterSave() {
        // update order total amount
        $this->order->update([
            // set total on order
            'total' => $this->order->total
                // add lines amount
                + $this->order->lines->sum('total') ]);
    }

}
