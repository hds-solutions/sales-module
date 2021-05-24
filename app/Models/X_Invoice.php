<?php

namespace HDSSolutions\Finpar\Models;

use HDSSolutions\Finpar\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Builder;

abstract class X_Invoice extends Base\Model {
    use BelongsToCompany;

    const PAYMENT_RULE_Cash     = 'CH';
    const PAYMENT_RULE_Credit   = 'CR';
    const PAYMENT_RULES = [
        self::PAYMENT_RULE_Cash     => 'sales::invoice.payment_rule.CH',
        self::PAYMENT_RULE_Credit   => 'sales::invoice.payment_rule.CR',
    ];

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
        'is_paid',
    ];

    protected $attributes = [
        'is_paid'   => false,
    ];

    protected $appends = [
        'payment_rule',
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
        'is_paid'           => [ 'sometimes', 'boolean' ],
    ];

    public function getIsSaleAttribute():bool {
        return !$this->is_purchase;
    }

    public function getPendingAmountAttribute() {
        return $this->total - $this->paid_amount;
    }

    public function getIsPaidAttribute():bool {
        return $this->attributes['is_paid'] || $this->pending_amount === 0;
    }

    public function getIsCashAttribute():bool {
        return !$this->is_credit;
    }

    public function getPaymentRuleAttribute():string {
        return __(self::PAYMENT_RULES[ $this->is_credit ? self::PAYMENT_RULE_Credit : self::PAYMENT_RULE_Cash ]);
    }

}
