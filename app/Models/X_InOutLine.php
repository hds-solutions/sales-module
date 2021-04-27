<?php

namespace HDSSolutions\Finpar\Models;

use Illuminate\Database\Eloquent\Builder;

class X_InOutLine extends Base\Model {

    protected $fillable = [
        'in_out_id',
        'order_line_id',
        'product_id',
        'variant_id',
        'locator_id',
        'quantity_ordered',
        'quantity_movement',
    ];


}
