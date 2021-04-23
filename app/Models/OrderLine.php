<?php

namespace HDSSolutions\Finpar\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Validator;

class OrderLine extends X_OrderLine
{
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function variant()
    {
        return $this->belongsTo(Variant::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function beforeSave(Validator $validator) {
        $this->original_price = $this->variant?->price($this->order->currency)?->pivot?->price ?? $this->product?->price($this->order->currency)?->pivot?->price;
        $this->total = $this->price * $this->quantity;
    }

    public function afterSave() {
        // update cash ending balande
        $this->order->update([
            'total' => $this->order->total
                // add lines amount
                + $this->order->lines->sum('total') ]);
    }

}
