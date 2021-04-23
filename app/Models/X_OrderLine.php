<?php

namespace HDSSolutions\Finpar\Models;

use HDSSolutions\Finpar\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Builder;

class OrderLine extends Base\Model{
    use BelongsToCompany;

    protected $fillable = [
        'order_id',
        'price',
        'quantity',
        'total',
        'currency_id',
        'conversion_rate',
    ];


}
