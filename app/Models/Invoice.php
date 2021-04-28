<?php

namespace HDSSolutions\Finpar\Models;

use HDSSolutions\Finpar\Interfaces\Document;
use HDSSolutions\Finpar\Traits\HasDocumentActions;
use HDSSolutions\Finpar\Traits\HasPartnerable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Validator;

class Invoice extends X_Invoice implements Document {
    use HasDocumentActions,
        HasPartnerable;

    public function branch() {
        return $this->belongsTo(Branch::class);
    }

    public function currency() {
        return $this->belongsTo(Currency::class);
    }

    public function employee() {
        return $this->belongsTo(Employee::class);
    }

    public function address() {
        return $this->belongsTo(Address::class);
    }

    public function lines() {
        return $this->hasMany(InvoiceLine::class);
    }

    public function scopeOverDue(Builder $query, int $graceDays = 0) {
        // return invoices that aren't paid
        return self::paid(false)
            // and transacted_at is over due-days
            ->where('transacted_at', '<=', today()->subDays( config('settings.due-days') + $days ));
    }

    public function scopeOverGrace(Builder $query, AsPerson $partnerable) {
        // return invoices that are overDue including Partnerable.graceDays
        return self::overDue( $partnerable->grace_days ));
    }

    public function scopePaid(Builder $query, bool $paid = true) {
        // return invoices that are paid
        return $query->where('is_paid', $paid);
    }

    public function beforeSave(Validator $validator) {
        // TODO: set employee from session
        if (!$this->exists) $this->employee()->associate( auth()->user() );

        // validations when document isCredit
        if ($this->is_credit && ($error = $this->creditValidations()) !== null)
            // reject invoice with error
            return $validator->errors()->add([
                'partnerable_id'    => __($error, [
                    'partner'   => $this->partnerable->full_name,
                ])
            ]);
    }

    public function prepareIt():?string {
        // when document isSale, validate invoiced qty <= ordered qty and credit available of Partnerable
        if ($this->is_sale) {
            // check that invoiced quantity of products isn't greater than ordered quantity
            foreach ($this->lines as $line) {
                // get pending quantity to invoice
                $quantity_pending = $line->orderLine->quantity_ordered - $line->orderLine->quantity_invoiced;
                // check if quantity invoiced > quantity pending
                if ($line->quantity_invoiced > $quantity_pending)
                    // reject document with error
                    return $this->documentError('sales::invoices.lines.invoiced-gt-pending', [
                        'product'   => $line->product->name,
                        'variant'   => $line->variant?->sku,
                    ]);
            }

            // when document isCredit, validate that Partnerable has enabled and available credit
            if ($this->is_credit && ($error = $this->creditValidations()) !== null)
                // return document error
                return $this->documentError($error, [
                    'partner'   => $this->partnerable->full_name,
                ]);
        }

        // return status InProgress
        return Document::STATUS_InProgress;
    }

    public function completeIt():?string {
        // update OrderLines with invoiced quantity. When isPurchase add invoiced to pending stock
        foreach ($this->lines as $line) {
            // update orderLine.quantity_invoiced
            $line->orderLine->quantity_invoiced += $line->quantity_invoiced;
            // check if ordered == invoiced and set orderline.invoiced=true
            if ($line->orderLine->quantity_invoiced == $line->orderLine->quantity_ordered)
                // change invoiced flag on line
                $line->orderLine->is_invoiced = true;
            // save orderLine changes
            if (!$line->orderLine->save())
                // redirect error
                return $this->documentError( $line->orderLine->errors()->first() );

            // when isPurchase, add invoiced quantity to pending stock
            if ($this->is_purchase) {
                // total quantity to set as pending
                $invoicedToPending = $line->quantity_invoiced;
                // get Variant|Product locators
                foreach (($line->variant ?? $line->product)->locators as $locator) {
                    // get storage for locator
                    $storage = Storage::getFromProductOnLocator($line->product, $line->variant, $locator);
                    // update pending stock for Variant|Product
                    $storage->pending += $invoicedToPending;
                    // save storage changes
                    if (!$storage->save())
                        // redirect error
                        return $this->documentError( $storage->errors()->first() );
                    // set pending to 0 (zero), all invoiced went to first storage found
                    $invoicedToPending = 0;
                    // exit loop
                    break;
                }
                // check if invoiced quantity was set on storage
                if ($invoicedToPending !== 0)
                    // reject with error
                    return $this->documentError('sales::invoices.lines.invoiced-to-pending-failed', [
                        'product'   => $line->product->name,
                        'variant'   => $line->variant?->sku,
                    ]);
            }
        }

        // return completed status
        return Document::STATUS_Completed;
    }

    private function creditValidations():?string {
        // check if Partner has credit enabled
        if (!$this->partnerable->has_credit_enabled)
            // return error
            return 'sales::invoices.partnerable-no-credit-enabled';

        // check if Partner has overdue invoices
        if (self::ofPartnerable($this->partnerable)->overGrace($this->partnerable)->count() > 0)
            // reject error
            return 'sales::invoices.partnerable-overdue-invoices';

        // check if Partner has available credit
        if ($this->partnerable->credit_available === 0)
            // reject error
            return 'sales::invoices.partnerable-no-credit-available';

        // no error, return null
        return null;
    }

}
