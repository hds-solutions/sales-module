<?php

namespace HDSSolutions\Laravel\Models;

use HDSSolutions\Laravel\Traits\BelongsToCompany;

abstract class X_InvoiceLineOrderLine extends Base\Pivot {
    use BelongsToCompany;

    protected $fillable = [
        'invoice_line_id',
        'order_line_id',
        'quantity_ordered',
        'quantity_invoiced',
    ];

}
