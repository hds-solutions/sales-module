<?php

namespace HDSSolutions\Finpar\Models;

use HDSSolutions\Finpar\Traits\BelongsToCompany;

abstract class X_InvoiceLineOrderLine extends Base\Pivot {
    use BelongsToCompany;

    protected $fillable = [
        'invoice_line_id',
        'order_line_id',
        'quantity_ordered',
        'quantity_invoiced',
    ];

}
