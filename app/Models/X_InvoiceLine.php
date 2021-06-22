<?php

namespace HDSSolutions\Finpar\Models;

use Illuminate\Database\Eloquent\Builder;

abstract class X_InvoiceLine extends Base\Model {

    protected $fillable = [
        'invoice_id',
        'currency_id',
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

    protected static array $rules = [
        'invoice_id'        => [ 'required' ],
        'currency_id'       => [ 'required' ],
        'product_id'        => [ 'required' ],
        'variant_id'        => [ 'sometimes', 'nullable' ],
        'price_reference'   => [ 'sometimes', 'nullable', 'min:0' ],
        'price_ordered'     => [ 'sometimes', 'nullable', 'min:0' ],
        'price_invoiced'    => [ 'required', 'min:0' ],
        'quantity_ordered'  => [ 'sometimes', 'nullable', 'min:0' ],
        'quantity_invoiced' => [ 'required', 'min:0' ],
        'quantity_received' => [ 'sometimes', 'nullable', 'min:0' ],
        'total'             => [ 'sometimes' ],
        'conversion_rate'   => [ 'sometimes', 'nullable', 'min:0' ],
    ];

}
