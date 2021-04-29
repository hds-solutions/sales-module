<?php

namespace HDSSolutions\Finpar\Models;

use HDSSolutions\Finpar\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Builder;

abstract class X_Invoice extends Base\Model {
    use BelongsToCompany;

    protected $fillable = [
        'branch_id',
        'currency_id',
        'employee_id',
        'partnerable_id',
        'partnerable_type',
        'address_id',
        'transacted_at',
        'stamping',
        'document_number',
        'is_purchase',
        'is_credit',
        'total',
        'paid_amount',
    ];

    protected static array $rules = [
        'branch_id'         => [ 'required' ],
        'currency_id'       => [ 'required' ],
        'employee_id'       => [ 'required' ],
        'partnerable_type'  => [ 'required' ],
        'partnerable_id'    => [ 'required' ],
        'address_id'        => [ 'sometimes' ],
        'transacted_at'     => [ 'sometimes' ],
        'stamping'          => [ 'sometimes' ],
        'document_number'   => [ 'required' ],
        'is_purchase'       => [ 'required', 'boolean' ],
        'is_credit'         => [ 'required', 'boolean' ],
        'total'             => [ 'sometimes' ],
        'paid_amount'       => [ 'sometimes' ],
    ];

    public function getIsSaleAttribute():bool {
        return !$this->is_purchase;
    }

    public function getIsPaidAttribute():bool {
        return $this->total - $this->paid_amount === 0;
    }

    public function getIsCashAttribute():bool {
        return !$this->is_credit;
    }

}
