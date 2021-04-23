<?php

namespace HDSSolutions\Finpar\Models;

use HDSSolutions\Finpar\Interfaces\Document;
use HDSSolutions\Finpar\Traits\HasDocumentActions;
use Illuminate\Database\Eloquent\Builder;

class Order extends X_Order implements Document {
    use HasDocumentActions;

    public function prepareIt():?string {

    }

    public function completeIt():?string {

    }

    public function lines() {
        return $this->hasMany(OrderLine::class);
    }

    public function partnerable() {
        return $this->morphTo(type: 'partnerable_type', id: 'partnerable_id');
    }

    public function currency() {
        return $this->belongsTo(Currency::class);
    }

}
