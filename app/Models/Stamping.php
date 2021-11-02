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

    public function getMaxDocumentNumberAttribute():?string {
        return $this->invoices->max('document_number');
    }

    public function getNextDocumentNumberAttribute():?string {
        return str_increment($this->max_document_number ?? null);
    }

    public function getNextDocumentNumber(string $prepend = '001-001-', bool $with_prepend = true):?string {
        // get next document number
        $next = str_increment($this->invoices()->where('document_number', 'like', "$prepend%")->max('document_number'));
        // return document number
        return $with_prepend ? $next : str_replace($prepend, '', $next);
    }

}
