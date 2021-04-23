<?php

namespace HDSSolutions\Finpar\Models;

use HDSSolutions\Finpar\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Builder;

class Order extends Base\Model
{
    use BelongsToCompany;

    protected $fillable = [
        'company_id',
        'partner_id',
        'partner_type',
        'branch_id',
        'currency_id',
        'address_id',
        'conversion_rate',
        'transaction_date',
        'total',
        'invoice_number',
        'stamping'
    ];

    public function details()
    {
        return $this->hasMany(X_OrderLine::class);
    }

}
