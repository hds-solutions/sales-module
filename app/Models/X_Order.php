<?php

namespace HDSSolutions\Laravel\Models;

use HDSSolutions\Laravel\Traits\BelongsToCompany;
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

    protected $appends = [
        'transacted_at_pretty',
    ];

    protected static array $rules = [
        'branch_id'         => [ 'required' ],
        'warehouse_id'      => [ 'required' ],
        'currency_id'       => [ 'required' ],
        'employee_id'       => [ 'required' ],
        'partnerable_type'  => [ 'required' ],
        'partnerable_id'    => [ 'required' ],
        'address_id'        => [ 'sometimes' ],
        'transacted_at'     => [ 'required', 'date', 'before_or_equal:now' ],
        'document_number'   => [ 'required', 'unique:orders,document_number,{id}' ],
        'is_purchase'       => [ 'required', 'boolean' ],
        'is_invoiced'       => [ 'required', 'boolean' ],
        'total'             => [ 'sometimes' ],
    ];

    public function getIsSaleAttribute():bool {
        return !$this->is_purchase;
    }

    public function getTransactedAtPrettyAttribute():string {
        return pretty_date($this->transacted_at, true);
    }

    public function getTotalAttribute():int|float {
        return $this->attributes['total'] / pow(10, $this->currency->decimals);
    }

    public function setTotalAttribute(int|float $total) {
        $this->attributes['total'] = $total * pow(10, $this->currency->decimals);
    }

}
