<?php

namespace HDSSolutions\Finpar\Models;

use HDSSolutions\Finpar\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Builder;

class X_InOut extends Base\Model {
    use BelongsToCompany;

    protected $fillable = [
        'branch_id',
        'warehouse_id',
        'employee_id',
        'partnerable_id',
        'partnerable_type',
        'invoice_id',
        'transacted_at',
        'stamping',
        'document_number',
        'is_purchase',
        'is_material_return',
        'is_complete',
    ];

    public function isPurchase():bool {
        return $this->is_purchase;
    }

    public function isMaterialReturn():bool {
        return $this->is_material_return;
    }

    public function isComplete():bool {
        return $this->is_purchase;
    }

}
