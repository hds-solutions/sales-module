<?php

namespace HDSSolutions\Laravel\Models;

use Illuminate\Database\Eloquent\Collection;

class Stamping extends X_Stamping {

    public function invoices() {
        return $this->hasMany(Invoice::class);
    }

}
