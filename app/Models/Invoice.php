<?php

namespace HDSSolutions\Laravel\Models;

use HDSSolutions\Laravel\Interfaces\Document;
use HDSSolutions\Laravel\Traits\HasDocumentActions;
use HDSSolutions\Laravel\Traits\HasPartnerable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Validator;

class Invoice extends X_Invoice implements Document {
    use HasDocumentActions,
        HasPartnerable;

    public function __construct(array|Order $attributes = []) {
        // check if is instance of Order
        if (($order = $attributes) instanceof Order) $attributes = self::fromOrder($order);
        // redirect attributes to parent
        parent::__construct(is_array($attributes) ? $attributes : []);
    }

    private static function fromOrder(Order $order):array {
        // copy attributes from Order
        return  [
            'branch_id'         => $order->branch_id,
            'warehouse_id'      => $order->warehouse_id,
            'currency_id'       => $order->currency_id,
            'employee_id'       => $order->employee_id,
            'partnerable_type'  => $order->partnerable_type,
            'partnerable_id'    => $order->partnerable_id,
            'transacted_at'     => $order->transacted_at,
            'is_purchase'       => $order->is_purchase,
        ];
    }

    public function branch() {
        return $this->belongsTo(Branch::class);
    }

    public function warehouse() {
        return $this->belongsTo(Warehouse::class);
    }

    public function currency() {
        return $this->belongsTo(Currency::class);
    }

    public function employee() {
        return $this->belongsTo(Employee::class);
    }

    public function stamping() {
        return $this->belongsTo(Stamping::class);
    }

    public function address() {
        return $this->belongsTo(Address::class);
    }

    public function lines() {
        return $this->hasMany(InvoiceLine::class);
    }

    public function orders() {
        return $this->hasManyDeep(Order::class, [
            InvoiceLine::class, InvoiceLineOrderLine::class,
            OrderLine::class,
        ], [
            null,
            'invoice_line_id',
            'id',
            'id',
        ], [
            null,
            'id',
            'order_line_id',
            'order_id',
        // prevent columns overlap
        ])->select('orders.*')->groupBy('orders.id');
    }

    public function receipments() {
        return $this->belongsToMany(Receipment::class, 'receipment_invoice')
            ->using(ReceipmentInvoice::class)
            ->withTimestamps()
            ->withPivot([ 'imputed_amount' ])
            ->as('receipmentInvoice');
    }

    // public function creditNotes() {
    //     return $this->hasManyThrough(CreditNote::class, MaterialReturn::class, null, 'documentable_id')
    //         ->where('documentable_type', MaterialReturn::class)
    //         ->select('credit_notes.*');
    // }

    public function materialReturns() {
        return $this->hasMany(MaterialReturn::class);
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
            // check valid stamping
            if (!$this->stamping->is_valid)
                // reject document
                return $this->documentError('sales::invoices.invalid-stamping', [
                    'stamping'  => $this->stamping->document_number,
                    'from'      => $this->stamping->valid_from,
                    'until'     => $this->stamping->valid_until,
                ]);

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
            // check if line is linked to any OrderLine
            // if so, update OrderLine.quantity_invoiced
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

                // update pending stock for Variant|Product on configured locators
                foreach (($line->variant ?? $line->product)->locators as $locator) {
                    // get storage for locator
                    $storage = Storage::getFromProductOnLocator($line->product, $line->variant, $locator);
                    // check if storage exists
                    if (!$storage->exists) $storage->save();
                    // update pending stock for Variant|Product
                    if (!$storage->update([ 'pending' => $storage->pending + $invoicedToPending ]))
                        // redirect error
                        return $this->documentError( $storage->errors()->first() );
                    // set pending to 0 (zero), all invoiced went to first storage found
                    $invoicedToPending = 0;
                    // exit loop
                    break;
                }

                // check if is remaining invoiced quantity (no locators are configured on Product|Variant)
                if ($invoicedToPending > 0)
                    // update pending stock for Variant|Product on existing locators
                    foreach (Storage::getFromProduct($line->product, $line->variant, $this->branch) as $storage) {
                        // update pending stock for Variant|Product
                        if (!$storage->update([ 'pending' => $storage->pending + $invoicedToPending ]))
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

        // create InOut document (only for purchases)
        if ($this->is_purchase) {
            // create InOut document
            if (!($inOut = InOut::createFromInvoice( $this, [ 'warehouse_id' => backend()->warehouse()->id ] ))->exists || $inOut->getDocumentError() !== null)
                // redirect inOut document error
                return $this->documentError( $inOut->getDocumentError() );
            // check if inOut hasn't lines (if OrderLines are only product.stockable=false)
            if ($inOut->lines->count() === 0)
                // delete empty InOut
                $inOut->delete();
        }

        // return completed status
        return Document::STATUS_Completed;
    }

    public static function createFromOrder(int|Order $order, array $attributes = []):self {
        // make invoice
        $resource = self::makeFromOrder($order, $attributes);

        // stop process if invoice can't be saved
        if (!$resource->save())
            // return error through document error
            return tap($resource, fn($resource) => $resource->documentError( $resource->errors()->first() ));

        // foreach lines
        foreach ($resource->lines as $line) {
            // link with parent
            $line->invoice()->associate($resource);
            // stop process if line can't be saved
            if (!$line->save())
                // return error through document error
                return tap($resource, fn($resource) => $resource->documentError( $line->errors()->first() ));

            // check if has orderLine
            if (isset($line->orderLine)) {
                // link OrderLine with InvoiceLine
                $line->orderLines()->attach($line->orderLine, [
                    'invoice_line_id'   => $line->id,
                    'quantity_ordered'  => $line->orderLine->quantity_ordered - $line->orderLine->quantity_invoiced,
                ]);
                // remove temporal relation
                $line->unsetRelation('orderLine');
            }
        }

        // return created invoice
        return $resource;
    }

    public static function makeFromOrder(int|Order $order, array $attributes = []):self {
        // load order if isn't instance
        if (!$order instanceof Order) $order = Order::findOrFail($order);

        // create new Invoice from Order
        $resource = new self($order);
        // append extra attributes
        $resource->fill( $attributes );

        // create InvoiceLines from OrderLines
        $order->lines->each(function($orderLine) use ($resource) {
            // create a new InvoiceLine from OrderLine
            $resource->lines->push( $line = new InvoiceLine($orderLine) );
            // set orderLine on temporal relation
            $line->setRelation('orderLine', $orderLine);
        });

        // return Invoice
        return $resource;
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
