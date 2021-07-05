<?php

namespace HDSSolutions\Finpar\Traits;

use HDSSolutions\Finpar\Contracts\AsPerson;
use Illuminate\Database\Eloquent\Builder;

trait HasPartnerable {

    public function partnerable() {
        return $this->morphTo(type: 'partnerable_type', id: 'partnerable_id')
            ->with([ 'identity' ]);
    }

    public function scopeOfPartnerable(Builder $query, int|AsPerson $partnerable) {
        // filter records that have partnerable relation set
        return $query->whereHas('partnerable', fn($partnerable_query)
            // filter partnerable id
            => $partnerable_query->where('partnerable_id',
                // set id accessing model.id or directly
                $partnerable instanceof AsPerson ? $partnerable->id : $partnerable));
    }

}
