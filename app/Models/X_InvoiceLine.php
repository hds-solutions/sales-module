<?php

namespace HDSSolutions\Finpar\Models;

use Illuminate\Database\Eloquent\Builder;

class X_InvoiceLine extends Base\Model {

    protected $fillable = [
        'invoice_id',
        'currency_id',
        'employee_id',
        'order_line_id',
        'product_id',
        'variant_id',
        'price_reference',
        'price_ordered',
        'price_invoiced',
        'quantity_ordered',
        'quantity_invoiced',
        'quantity_received',
        'total',
        'conversion_rate',
    ];


}
