<?php

namespace HDSSolutions\Finpar\Models;

use Illuminate\Database\Eloquent\Collection;

class InvoiceLineOrderLine extends X_InvoiceLineOrderLine {

    public function invoiceLine() {
        return $this->belongsTo(InvoiceLine::class);
    }

    public function orderLine() {
        return $this->belongsTo(OrderLine::class);
    }

}
