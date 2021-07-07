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

    public static function currentStamping():?string {
        // return latest stamping number used
        return self::select('stamping')->orderByDesc('transacted_at')->first()?->stamping ?? null;
    }

    public static function nextDocumentNumber(string $stamping = null):string {
        // return next document number for specified stamping
        return str_increment(self::where('stamping', $stamping)->max('document_number') ?? null);
    }

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
                'transacted_at'     => $order->transacted_at,
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

    public function receipments() {
        return $this->belongsToMany(Receipment::class, 'receipment_invoice')
            ->using(ReceipmentInvoice::class)
            ->withTimestamps()
            ->withPivot([ 'imputed_amount' ])
            ->as('receipmentInvoice');
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
            if (!$line->orderLines->count()) continue;
            // check if quantity invoiced > ordered quantity
            if ($line->quantity_invoiced > $line->orderLines->sum('pivot.quantity_ordered'))
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
            // check if line is linked to OrderLine
            // is so, update OrderLine.quantity_invoiced
            if ($line->orderLines->count()) {
                // save invoiced quantoty for later validations
                $quantity_pending = $line->quantity_invoiced;
                // update every OrderLine.quantity_invoiced
                foreach ($line->orderLines as $orderLine) {
                    // calculate available quantity for current OrderLine
                    $quantity_to_invoice = $orderLine->pivot->quantity_ordered < $quantity_pending
                        ? $orderLine->pivot->quantity_ordered
                        : $quantity_pending;
                    // add available invoiced quantity to OrderLine
                    $orderLine->quantity_invoiced += $quantity_to_invoice;
                    // change invoiced flag if ordered == invoiced
                    $orderLine->is_invoiced = $orderLine->quantity_ordered == $orderLine->quantity_invoiced;
                    // save orderLine changes
                    if (!$orderLine->save())
                        // redirect error
                        return $this->documentError( $orderLine->errors()->first() );
                    // check if all lines of order where invoiced
                    if ($orderLine->order->lines()->invoiced(false)->count() === 0)
                        // mark order as invoiced
                        if (!$orderLine->order->update([ 'is_invoiced' => true ]))
                            // return order saving error
                            return $this->documentError( $orderLine->order->errors()->first() );
                    // update invoiced quantity on pivot
                    $line->orderLines()->updateExistingPivot($orderLine->id, [
                        'quantity_invoiced' => $quantity_to_invoice,
                    ]);
                    // substract invoiced from pendint
                    $quantity_pending -= $quantity_to_invoice;
                    // check if already invoiced all pending quantity and exit loop
                    if ($quantity_pending == 0) break;
                }
                // check if there is remaining quantity to invoice
                if ($quantity_pending > 0)
                    // reject with error
                    return $this->documentError('sales::invoices.lines.invoiced-to-orderlines-failed', [
                        'product'   => $line->product->name,
                        'variant'   => $line->variant?->sku,
                    ]);
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
        // make invoice
        $invoice = self::makeFromOrder($order, $attributes);
        // stop process if invoice can't be saved
        if (!$invoice->save()) return $invoice;
        // foreach lines
        foreach ($invoice->lines as $invoiceLine) {
            // link with parent
            $invoiceLine->invoice()->associate($invoice);
            // stop process if line can't be saved
            if (!$invoiceLine->save()) return $invoice;
            // check if has orderLine
            if (isset($invoiceLine->orderLine)) {
                // link OrderLine with InvoiceLine
                $invoiceLine->orderLines()->attach($invoiceLine->orderLine, [
                    'invoice_line_id'   => $invoiceLine->id,
                    'quantity_ordered'  => $invoiceLine->orderLine->quantity_ordered - $invoiceLine->orderLine->quantity_invoiced,
                ]);
                // remove temporal relation
                $invoiceLine->unsetRelation('orderLine');
            }
        }

        // return created invoice
        return $invoice;
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
            // set temporal orderLine on temporal relation
            $invoiceLine->setRelation('orderLine', $orderLine);
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
