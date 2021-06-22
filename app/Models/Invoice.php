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

    public function __construct(array|Order $attributes = []) {
        // check if is instance of Order
        if (($order = $attributes) instanceof Order)
            // copy attributes from Order
            $attributes = [
                'branch_id'         => $order->branch_id,
                'currency_id'       => $order->currency_id,
                'employee_id'       => $order->employee_id,
                'partnerable_type'  => $order->partnerable_type,
                'partnerable_id'    => $order->partnerable_id,
                'is_purchase'       => $order->is_purchase,
            ];
        // redirect attributes to parent
        parent::__construct(is_array($attributes) ? $attributes : []);
    }

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
        return self::overDue( $partnerable->grace_days );
    }

    public function scopePaid(Builder $query, bool $paid = true) {
        // return invoices that are paid
        return $query->where('is_paid', $paid);
    }

    public function hasProduct(int|Product $product, int|Variant|null $variant = null) {
        // get invoice lines
        $lines = $this->lines();

        // filter product
        $lines->where('product_id', $product instanceof Product ? $product->id : $product);
        // filter variant if specified
        if ($variant !== null) $lines->where('variant_id', $variant instanceof Variant ? $variant->id : $variant);
        else $lines->whereNull('variant_id');

        // return if there is lines with specified product|variant
        return $lines->count() > 0;
    }

    public function beforeSave(Validator $validator) {
        // TODO: set employee from session
        if (!$this->exists && $this->employee === null) $this->employee()->associate( auth()->user() );

        // validations when document isCredit
        if ($this->is_credit && ($error = $this->creditValidations()) !== null)
            // reject invoice with error
            return $validator->errors()->add('partnerable_id', __($error, [
                'partner'   => $this->partnerable->full_name,
            ]));

        // total must have value
        $this->total = $this->total ?? 0;
    }

    public function prepareIt():?string {
        // check if document has lines
        if (!$this->lines()->count()) return $this->documentError('sales::invoice.no-lines');

        // check that invoiced quantity of products isn't greater than ordered quantity
        foreach ($this->lines as $line) {
            // ignore line if wasn't created from Order
            if ($line->orderLine === null) continue;
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

        // when document isSale, validate credit available of Partnerable
        if ($this->is_sale) {
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
            // check if line is linked to an OrderLine
            if ($line->orderLine !== null) {
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
                // check if all lines of order where invoiced
                if ($line->orderLine->order->lines()->invoiced(false)->count() === 0)
                    // mark order as invoiced
                    if (!$line->orderLine->order->update([ 'is_invoiced' => true ]))
                        // return order saving error
                        return $this->documentError( $line->orderLine->order->errors()->first() );
            }

            // when isPurchase, add invoiced quantity to pending stock (only for products that are stockables)
            if ($this->is_purchase && $line->product->stockable) {
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

    public static function createFromOrder(int|Order $order, array $attributes = []):Invoice {
        // make and save invoice
        return tap(self::makeFromOrder($order, $attributes), function($invoice) {
            // save invoice
            $invoice->save();
            // save Invoice.lines
            $invoice->lines->each(function($invoiceLine) use ($invoice) {
                // link with parent
                $invoiceLine->invoice()->associate($invoice);
                // save InvoiceLine
                $invoiceLine->save();
            });
        });
    }

    public static function makeFromOrder(int|Order $order, array $attributes = []):Invoice {
        // load order if isn't instance
        if (!$order instanceof Order) $order = Order::findOrFail($order);
        // create new Invoice from Order
        $invoice = new self($order);
        // append extra attributes
        $invoice->fill( $attributes );
        // create InvoiceLines from OrderLines
        $order->lines->each(function($orderLine) use ($invoice) {
            // create a new InvoiceLine from OrderLine
            $invoice->lines->push( $invoiceLine = new InvoiceLine($orderLine) );
        });
        // return Invoice
        return $invoice;
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
