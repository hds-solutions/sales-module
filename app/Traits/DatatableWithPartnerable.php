<?php

namespace HDSSolutions\Laravel\Traits;

use Illuminate\Database\Eloquent\Builder;

trait DatatableWithPartnerable {

    protected function orderPartnerableFullName(Builder $query, string $order):Builder {
        // add custom orderBy for column Partnerable.full_name
        return $query
            // order by Partnerable.full_name (Customer.business_name > People.lastname + People.firstname > People.firstname)
            ->orderByRaw(
                // order by business_name if has it
                "CASE WHEN customers.business_name IS NOT NULL THEN customers.business_name END $order, ".
                // order by lastname + firstname if has lastname
                "CASE WHEN people.lastname IS NOT NULL THEN people.lastname END $order, ".
                // order by firstname as default
                "people.firstname $order");
    }

    protected function searchPartnerableFullName(Builder $query, string $value):Builder {
        // return custom search for Partner.full_name
        return $query
            // find by Customer.business_name
            ->where('customers.business_name', 'like', "%$value%")
            // find by People.lastname
            ->orWhere('people.lastname', 'like', "%$value%")
            // fint by People.firstname
            ->orWhere('people.firstname', 'like', "%$value%");
    }

    protected function filterPartnerable(Builder $query, $partnerable_id):Builder {
        // filter only from partnerable
        return $query->where('partnerable_id', $partnerable_id);
    }

    protected abstract function joins(Builder $query):Builder;

}
