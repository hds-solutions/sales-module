<?php

namespace HDSSolutions\Laravel\Models;

use HDSSolutions\Laravel\Traits\BelongsToCompany;

abstract class X_Stamping extends Base\Model {
    use BelongsToCompany;

    protected $fillable = [
        'document_number',
        'valid_from',
        'valid_until',
    ];

}
