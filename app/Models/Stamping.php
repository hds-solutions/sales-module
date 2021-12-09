<?php

namespace HDSSolutions\Laravel\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Builder;

class Stamping extends X_Stamping {

    public function invoices() {
        return $this->hasMany(Invoice::class);
    }

    public function scopeValid(Builder $query) {
        // return valid stampings
        return $query->where('valid_from', '<', now())->where('valid_until', '>=', now());
    }

    public function scopeIsPurchase(Builder $query, bool $is_purchase = true) {
        // return only stampings
        return $query->where('is_purchase', $is_purchase);
    }

    public function scopeIsSale(Builder $query, bool $is_sale = true) {
        // return inverted filter
        return $this->scopeIsPurchase($query, !$is_sale);
    }

}
