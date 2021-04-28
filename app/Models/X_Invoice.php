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
        'is_paid',
        'paid_amount',
    ];

    public function isPurchase():bool {
        return $this->is_purchase;
    }

    public function getIsSaleAttribute():bool {
        return !$this->is_purchase;
    }

    public function isSale():bool {
        return $this->is_sale;
    }

    public function isPaid():bool {
        return $this->is_paid;
    }

    public function getIsCashAttribute():bool {
        return !$this->is_credit;
    }

    public function isCash():bool {
        return $this->is_cash;
    }

}
