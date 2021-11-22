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
        return self::where('valid_from', '<', now())->where('valid_until', '>=', now());
    }

    public function scopeIsPurchase(Builder $query, bool $is_purchase = true) {
        // return only stampings
        return self::where('is_purchase', $is_purchase);
    }

    public function scopeIsSale(Builder $query, bool $is_sale = true) {
        // return inverted filter
        return $this->scopeIsPurchase($query, !$is_sale);
    }

    public function getMaxDocumentNumberAttribute():?string {
        return $this->invoices->max('document_number');
    }

    public function getNextDocumentNumberAttribute():?string {
        return str_increment($this->max_document_number ?? null);
    }

    public function getNextDocumentNumber(string $prepend = '001-001-', bool $with_prepend = true):?string {
        // get next document number
        $next = str_increment( str_pad($this->current ?? ($this->start - 1), $this->length, 0, STR_PAD_LEFT) );
        // save current document number
        $this->update([ 'current' => $next = str_replace($prepend, '', $next) ]);
        // return document number
        return $with_prepend ? $prepend.$next : $next;
    }

}
