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

}
