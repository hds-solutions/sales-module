<?php

namespace HDSSolutions\Finpar\Models;

use HDSSolutions\Finpar\Traits\BelongsToCompany;

abstract class X_Empty extends Base\Model {
    use BelongsToCompany;

    protected array $orderBy = [
        'name'  => 'ASC',
    ];

    protected $fillable = [
        'name',
    ];

    protected static array $rules = [
        'name'  => [ 'required' ],
    ];

}
