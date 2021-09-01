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

    public function getMaxDocumentNumberAttribute() {
        return $this->invoices->max('document_number');
    }

    public function getNextDocumentNumberAttribute() {
        return str_increment($this->max_document_number ?? null);
    }

}
