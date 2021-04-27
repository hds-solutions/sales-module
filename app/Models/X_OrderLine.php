<?php

namespace HDSSolutions\Finpar\Models;

use Illuminate\Database\Eloquent\Builder;

abstract class X_OrderLine extends Base\Model {

    protected $fillable = [
        'order_id',
        'currency_id',
        'employee_id',
        'product_id',
        'variant_id',
        'price_reference',
        'price_ordered',
        'quantity_ordered',
        'quantity_invoiced',
        'total',
        'is_invoiced',
        'conversion_rate',
    ];


}
