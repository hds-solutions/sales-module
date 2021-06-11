<?php

namespace HDSSolutions\Finpar\Models;

use HDSSolutions\Finpar\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Builder;

abstract class X_Order extends Base\Model {
    use BelongsToCompany;

    protected $fillable = [
        'branch_id',
        'warehouse_id',
        'currency_id',
        'employee_id',
        'partnerable_id',
        'partnerable_type',
        'address_id',
        'transacted_at',
        'document_number',
        'is_purchase',
        'is_invoiced',
        'total',
    ];

    protected $attributes = [
        'is_invoiced'   => false,
    ];

    protected static array $rules = [
        'branch_id'         => [ 'required' ],
        'warehouse_id'      => [ 'required' ],
        'currency_id'       => [ 'required' ],
        'employee_id'       => [ 'required' ],
        'partnerable_type'  => [ 'required' ],
        'partnerable_id'    => [ 'required' ],
        'address_id'        => [ 'sometimes' ],
        'transacted_at'     => [ 'sometimes', 'date', 'before:now' ],
        'document_number'   => [ 'required' ],
        'is_purchase'       => [ 'required', 'boolean' ],
        'is_invoiced'       => [ 'required', 'boolean' ],
        'total'             => [ 'sometimes' ],
    ];

    public function getIsSaleAttribute():bool {
        return !$this->is_purchase;
    }

}
