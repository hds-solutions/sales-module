<?php

namespace HDSSolutions\Laravel\Traits;

use Illuminate\Database\Eloquent\Builder;

trait DatatableWithCurrency {

    protected function orderCurrencyName(Builder $query, string $order):Builder {
        // add custom orderBy for column Currency.name
        return $query->orderBy('currencies.name', $order);
    }

    protected function filterCurrency(Builder $query, int $currency_id):Builder {
        // filter only from partnerable
        return $query->where('currency_id', $currency_id);
    }

    protected abstract function joins(Builder $query):Builder;

}
