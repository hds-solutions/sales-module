<?php

namespace HDSSolutions\Finpar\Models;

use HDSSolutions\Finpar\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Builder;

abstract class X_Order extends Base\Model {
    use BelongsToCompany;

    protected $fillable = [
        'branch_id',
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

    public function isPurchase():bool {
        return $this->is_purchase;
    }

    public function getIsSaleAttribute():bool {
        return !$this->is_purchase;
    }

    public function isSale():bool {
        return $this->is_sale;
    }

    public function isInvoiced():bool {
        return $this->is_invoiced;
    }

}
