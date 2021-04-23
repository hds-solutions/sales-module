<?php

namespace HDSSolutions\Finpar\Models;

use Illuminate\Database\Eloquent\Builder;

class X_OrderLine extends Base\Model{

    protected $fillable = [
        'order_id',
        'original_price',
        'price',
        'quantity',
        'total',
        'currency_id',
        'conversion_rate',
        'product_id',
        'variant_id'
    ];


}
