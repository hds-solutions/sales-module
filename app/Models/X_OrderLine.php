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

    protected $attributes = [
        'is_invoiced'   => false,
    ];

    protected static array $rules = [
        'order_id'          => [ 'required' ],
        'currency_id'       => [ 'required' ],
        'employee_id'       => [ 'required' ],
        'product_id'        => [ 'required' ],
        'variant_id'        => [ 'sometimes', 'nullable' ],
        'price_reference'   => [ 'required', 'min:0' ],
        'price_ordered'     => [ 'required', 'min:0' ],
        'quantity_ordered'  => [ 'required', 'min:0' ],
        'quantity_invoiced' => [ 'sometimes', 'nullable', 'min:0' ],
        'total'             => [ 'sometimes' ],
        'is_invoiced'       => [ 'required', 'boolean' ],
        'conversion_rate'   => [ 'sometimes', 'nullable', 'min:0' ],
    ];

}
