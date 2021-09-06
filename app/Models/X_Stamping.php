<?php

namespace HDSSolutions\Laravel\Models;

use HDSSolutions\Laravel\Traits\BelongsToCompany;

abstract class X_Stamping extends Base\Model {
    use BelongsToCompany;

    protected $orderBy = [
        'valid_until'       => 'DESC',
        'valid_from'        => 'DESC',
        'document_number'   => 'ASC',
    ];

    protected $fillable = [
        'document_number',
        'valid_from',
        'valid_until',
    ];

    protected $casts = [
        'valid_from'    => 'datetime',
        'valid_until'   => 'datetime',
    ];

    protected $appends = [
        'valid_from_pretty',
        'valid_until_pretty',
    ];

    public function getValidFromPrettyAttribute():string {
        return pretty_date($this->valid_from);
    }

    public function getValidUntilPrettyAttribute():string {
        return pretty_date($this->valid_until);
    }

    public function getIsValidAttribute():bool {
        return $this->valid_from < now() && $this->valid_until >= now();
    }

}
