<?php

namespace HDSSolutions\Finpar\Models;

use HDSSolutions\Finpar\Interfaces\Document;
use HDSSolutions\Finpar\Traits\HasDocumentActions;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Validator;

class Order extends X_Order implements Document {
    use HasDocumentActions;

    public function branch() {
        return $this->belongsTo(Branch::class);
    }

    public function currency() {
        return $this->belongsTo(Currency::class);
    }

    public function employee() {
        return $this->belongsTo(Employee::class);
    }

    public function partnerable() {
        return $this->morphTo(type: 'partnerable_type', id: 'partnerable_id');
    }

    public function address() {
        return $this->belongsTo(Address::class);
    }

    public function lines() {
        return $this->hasMany(OrderLine::class);
    }

    public function beforeSave(Validator $validator) {
        // TODO: Check drafted order from more than XX days

        // TODO: set employee from session
        if (!$this->exists) $this->employee()->associate( auth()->user() );
    }

    public function prepareIt():?string {
        // TODO: check lines count
        // TODO: if isSale=true
            // TODO: for each line
                // TODO: check if product is sold
                // TODO: check available stock
                // TODO: check drafted inventories
        return null;
    }

    public function completeIt():?string {
        // TODO: if isSale=true
            // TODO: for each line:
                // TODO: reserve stock of Variant|Product
        // TODO: create InOut document
        return null;
    }

}
