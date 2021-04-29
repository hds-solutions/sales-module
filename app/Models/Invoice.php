<?php

namespace HDSSolutions\Finpar\Models;

use HDSSolutions\Finpar\Interfaces\Document;
use HDSSolutions\Finpar\Traits\HasDocumentActions;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Validator;

class Invoice extends X_Invoice implements Document {
    use HasDocumentActions;

    public function branch() {
        return $this->belongsTo(Branch::class);
    }

    public function currency() {
        return $this->belongsTo(Currency::class);
    }

    public function employee() {
        return $this->belongsTo(Employee::class);
    }

    public function partnerable() {
        return $this->morphTo(type: 'partnerable_type', id: 'partnerable_id');
    }

    public function address() {
        return $this->belongsTo(Address::class);
    }

    public function lines() {
        return $this->hasMany(InvoiceLine::class);
    }

    public function beforeSave(Validator $validator) {
        // TODO: when isCredit=true
            // TODO: Check Partner enabled for credit
            // TODO: Check Partner overdue invoices
            // TODO: Check Partner available credit

        // TODO: set employee from session
        if (!$this->exists) $this->employee()->associate( auth()->user() );
    }

    public function prepareIt():?string {
        // TODO: if isSale=true
            // TODO: for each line
                // TODO: Check invoiced quantity <= ordered quantity
            // TODO: if isCredit=true
                // TODO: Check Partner enabled for credit
                // TODO: Check Partner overdue invoices
                // TODO: Check Partner available credit

        return null;
    }

    public function completeIt():?string {
        // TODO: for each line
            // TODO: update orderLine.quantity_invoiced
            // TODO: if ordered == invoiced set orderline.invoiced=true
            // TODO: if isPurchase=true
                // TODO: update pending stock for Variant|Product

        return null;
    }

}
