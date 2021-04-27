<?php

namespace HDSSolutions\Finpar\Models;

use HDSSolutions\Finpar\Interfaces\Document;
use HDSSolutions\Finpar\Traits\HasDocumentActions;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Validator;

class InOut extends X_InOut implements Document {
    use HasDocumentActions;

    public function branch() {
        return $this->belongsTo(Branch::class);
    }

    public function warehouse() {
        return $this->belongsTo(Warehouse::class);
    }

    public function employee() {
        return $this->belongsTo(Employee::class);
    }

    public function partnerable() {
        return $this->morphTo(type: 'partnerable_type', id: 'partnerable_id');
    }

    public function invoice() {
        return $this->belongsTo(Invoice::class);
    }

    public function lines() {
        return $this->hasMany(InOutLine::class);
    }

    public function beforeSave(Validator $validator) {
        // TODO: set employee from session
        if (!$this->exists) $this->employee()->associate( auth()->user() );

        // if document is material return and invoice not set
        if ($this->isMaterialReturn && $this->invoice === null)
            // reject it, Invoice must be specified when returning
            $validator->errors()->add('invoice_id', __('sales::inout.material-return-invoice'));
    }

    public function afterSave() {
        // TODO: if isMaterialReturn=true, create lines from invoice
    }

    public function prepareIt():?string {
        // TODO: if isMaterialReturn=true
            // TODO: InOut of order must be completed to return items

        // TODO: if isSale=true
            // TODO: for each line
                // TODO: if isMaterialReturn=true && line.quantity_movement == 0, reject since can't return empty lines

        return null;
    }

    public function completeIt():?string {
        // TODO: let isComplete = true
        // TODO: for each line
            // TODO: if line.ordered !== movement, isComplete=false
            // TODO: if isMaterialReturn=true
                // TODO: check if returned quantity is greater than invoiced quantity and reject it

            // TODO: let quantityToMove = line.quantity_movement
            // TODO: for each line.product.locators
                // TODO: let availableOnStorage = Storage.qty( locator )

                // TODO: if isMaterialReturn=true
                    // TODO: add storage.onHand
                    // TODO: quantityToMove=0
                // TODO: if isSale=true
                    // TODO: substract availableOnStorage from storage.onHand
                    // TODO: substract availableOnStorage from storage.reserved
                    // TODO: quantityToMove -= availableOnStorage
                // TODO: if isPurchase=true
                    // TODO: add storage.onHand
                    // TODO: substract storage.pending
                    // TODO: quantityToMove=0
                // TODO: if quantityToMove == 0, exit loop

            // TODO: if quantityToMove != 0, reject it, can't substract stock

        // TODO: set this.isComplete( isComplete )

        // TODO: if isMaterialReturn, create creditNote

        return null;
    }

}
