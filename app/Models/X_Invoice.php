<?php

namespace HDSSolutions\Laravel\Models;

use HDSSolutions\Laravel\Traits\BelongsToCompany;
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
        'warehouse_id',
        'currency_id',
        'price_list_id',
        'employee_id',
        'partnerable_id',
        'partnerable_type',
        'address_id',
        'transacted_at',
        'stamping_id',
        'document_number',
        'is_purchase',
        'is_credit',
        'total',
        'paid_amount',
        'is_paid',
    ];

    protected $attributes = [
        'is_paid'   => false,
        'total'     => 0,
    ];

    protected $appends = [
        'payment_rule',
        'transacted_at_pretty',
        'total_pretty',
    ];

    protected static array $rules = [
        'branch_id'         => [ 'required' ],
        'warehouse_id'      => [ 'sometimes', 'nullable' ],
        'currency_id'       => [ 'required' ],
        'price_list_id'     => [ 'required' ],
        'employee_id'       => [ 'required' ],
        'partnerable_type'  => [ 'required' ],
        'partnerable_id'    => [ 'required' ],
        'address_id'        => [ 'sometimes' ],
        'transacted_at'     => [ 'required', 'date', 'before_or_equal:now' ],
        'stamping_id'       => [ 'sometimes' ],
        'document_number'   => [ 'required', 'unique:invoices,document_number,{id},id,stamping_id,{stamping_id}' ],
        'is_purchase'       => [ 'required', 'boolean' ],
        'is_credit'         => [ 'required', 'boolean' ],
        'total'             => [ 'sometimes' ],
        'paid_amount'       => [ 'sometimes' ],
        'is_paid'           => [ 'sometimes', 'boolean' ],
    ];

    public function getIsSaleAttribute():bool {
        return !$this->is_purchase;
    }

    public function getTotalAttribute():int|float {
        return $this->attributes['total'] / pow(10, currency($this->currency_id)->decimals);
    }

    public function setTotalAttribute(int|float $total) {
        $this->attributes['total'] = $total * pow(10, currency($this->currency_id)->decimals);
    }

    public function getPaidAmountAttribute():int|float {
        return $this->attributes['paid_amount'] / pow(10, currency($this->currency_id)->decimals);
    }

    public function setPaidAmountAttribute(int|float $paid_amount) {
        $this->attributes['paid_amount'] = $paid_amount * pow(10, currency($this->currency_id)->decimals);
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

    public function getTransactedAtPrettyAttribute():string {
        return pretty_date($this->transacted_at, true);
    }

    public function getTotalPrettyAttribute():string {
        return amount($this->total, $this->currency_id);
    }

}
