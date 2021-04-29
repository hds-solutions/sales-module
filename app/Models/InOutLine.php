<?php

namespace HDSSolutions\Finpar\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Validator;

class InOutLine extends X_InOutLine {

    public function inOut() {
        return $this->belongsTo(InOut::class);
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
        // TODO: check if there is drafted Inventories of Variant|Product
        // TODO: check if inOut already has a line with current Variant|Product
    }

}
