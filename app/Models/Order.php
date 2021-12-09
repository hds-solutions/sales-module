<?php

namespace HDSSolutions\Laravel\Models;

use HDSSolutions\Laravel\Interfaces\Document;
use HDSSolutions\Laravel\Traits\HasDocumentActions;
use HDSSolutions\Laravel\Traits\HasPartnerable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Validator;

class Order extends X_Order implements Document {
    use HasDocumentActions,
        HasPartnerable;

    public function __construct(array $attributes = []) {
        // redirect attributes to parent
        parent::__construct($attributes);
        // allow void this document
        $this->document_enableVoidIt = true;
    }

    public static function nextDocumentNumber(bool $is_purchase = false):?string {
        // return next document number for specified stamping
        return str_increment(self::isPurchase($is_purchase)->withTrashed()->max('document_number'));
    }

    public function branch() {
        return $this->belongsTo(Branch::class)
            ->withTrashed();
    }

    public function warehouse() {
        return $this->belongsTo(Warehouse::class)
            ->withTrashed();
    }

    public function currency() {
        return $this->belongsTo(Currency::class)
            ->withTrashed();
    }

    public function priceList() {
        return $this->belongsTo(PriceList::class)
            ->withTrashed();
    }

    public function employee() {
        return $this->belongsTo(Employee::class)
            ->withTrashed();
    }

    public function address() {
        return $this->belongsTo(Address::class)
            ->withTrashed();
    }

    public function lines() {
        return $this->hasMany(OrderLine::class);
    }

    public function inOut() {
        // return inOut of order
        return $this->hasOne(InOut::class);
    }

    public function scopeInvoiced(Builder $query, bool $invoiced = true) {
        return $query->where('is_invoiced', $invoiced);
    }

    public function scopeIsPurchase(Builder $query, bool $is_purchase = true) {
        return $query->where('is_purchase', $is_purchase);
    }

    public function scopeIsSale(Builder $query, bool $is_sale = true) {
        return $this->scopeIsPurchase($query, !$is_sale);
    }

    public function hasProduct(int|Product $product, int|Variant|null $variant = null) {
        // get order lines
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

        // check if document age validations are enabled
        if (config('settings.validate-orders-age')) {
            // check drafted order from more than XX days ago
            if (self::drafted()->createdAgo( config('settings.pending-documents-age') )->count() > 0)
                // reject order with error
                return $validator->errors()->add('id', __('sales::order.beforeSave.drafted-created-ago', [
                    'days'  => config('settings.pending-documents-age'),
                ]));

            // check if there is orders pending for invoiceIt from more than XX days ago
            if (self::invoiced(false)->createdAgo( config('settings.pending-documents-age') )->count() > 0)
                // reject order with error
                return $validator->errors()->add('id', __('sales::order.beforeSave.not-invoiced-created-ago', [
                    'days'  => config('settings.pending-documents-age'),
                ]));
        }

        // total must have value
        $this->total = $this->total ?? 0;
    }

    public function prepareIt():?string {
        // check if document has lines
        if (!$this->lines()->count()) return $this->documentError('sales::order.prepareIt.no-lines');

        // line validations only for sale document
        if ($this->is_sale) foreach ($this->lines as $line) {
            // check if product is sold
            if (!$line->product->type->is_sold)
                // return document error
                return $this->documentError('sales::order.prepareIt.product-isnt-sold', [
                    'product'   => $line->product->name,
                    'variant'   => $line->variant?->sku,
                ]);

            // validations for products that has stock
            if ($line->product->stockable) {
                // check if there are drafted Inventories of Variant|Product
                if (Inventory::hasOpenForProduct( $line->product, $line->variant, $this->branch ))
                    // reject line with error
                    return $this->documentError('sales::order.prepareIt.pending-inventories', [
                        'product'   => $line->product->name,
                        'variant'   => $line->variant?->sku,
                        'branch'    => $this->branch->name,
                    ]);

                // check available stock of Variant|Product
                if ($line->quantity_ordered > ($available = Storage::getQtyAvailable( $line->product, $line->variant, $this->branch )))
                    // reject line with error
                    return $this->documentError('sales::order.prepareIt.no-enough-stock', [
                        'product'   => $line->product->name,
                        'variant'   => $line->variant?->sku,
                        'available' => $available,
                    ]);
            }
        }

        // return status InProgress
        return self::STATUS_InProgress;
    }

    public function rejectIt():bool {
        // mark document as rejected
        return true;
    }

    public function completeIt():?string {
        // check if document is purchase
        // if so, no extra process needed
        if ($this->is_purchase)
            // purchase Orders don't do any here,
            // we are safe to complete the completeIt process
            return self::STATUS_Completed;

        // if document is sale, reserve stock and create InOut document
        // reserve stock of Variants|Products (only when document is sale)
        foreach ($this->lines as $line) {
            // ignore line if product.type isn't stockable
            if (!$line->product->stockable) continue;

            // total quantity to reserve
            $pendingToReserve = $line->quantity_ordered;

            // reserve stock for Variant|Product on configured locators
            foreach (($line->variant ?? $line->product)->locators as $locator) {
                // check if locator belongs to current branch
                if ($locator->warehouse->branch_id !== $this->branch_id) continue;
                // check if storage has available stock
                if (($storage = Storage::getFromProductOnLocator($line->product, $line->variant, $locator))->available > 0) {
                    // calculate available stock to reserve on current storage
                    $availableToReserve = $storage->available > $pendingToReserve ? $pendingToReserve : $storage->available;
                    // reserve stock on storage
                    if (!$storage->update([ 'reserved' => $storage->reserved + $availableToReserve ]))
                        // return document error
                        return $this->documentError( $storage->errors()->first() );
                    // reduce pending quantity to reserve
                    $pendingToReserve -= $availableToReserve;
                    // if all pending quantity was already reserved, exit loop
                    if ($pendingToReserve === 0) break;
                }
            }

            // reserve stock for Variant|Product on existing Storages
            foreach (Storage::getFromProduct($line->product, $line->variant, $this->branch) as $storage) {
                // check if storage has available stock
                if ($storage->available > 0) {
                    // calculate available stock to reserve on current storage
                    $availableToReserve = $storage->available > $pendingToReserve ? $pendingToReserve : $storage->available;
                    // reserve stock on storage
                    if (!$storage->update([ 'reserved' => $storage->reserved + $availableToReserve ]))
                        // return document error
                        return $this->documentError( $storage->errors()->first() );
                    // reduce pending quantity to reserve
                    $pendingToReserve -= $availableToReserve;
                    // if all pending quantity was already reserved, exit loop
                    if ($pendingToReserve === 0) break;
                }
            }

            // if there is pending quantity, reject document process
            if ($pendingToReserve !== 0)
                // reject with error
                return $this->documentError('sales::order.completeIt.pending-to-reserve', [
                    'product'   => $line->product->name,
                    'variant'   => $line->variant?->sku,
                ]);
        }

        // create InOut document
        if (!($inOut = InOut::createFromOrder( $this ))->exists || $inOut->getDocumentError() !== null)
            // redirect inOut document error
            return $this->documentError( $inOut->getDocumentError() );
        // check if inOut hasn't lines (if OrderLines are only product.stockable=false)
        if ($inOut->lines->count() === 0)
            // delete empty InOut
            $inOut->delete();

        // return completed status
        return self::STATUS_Completed;
    }

    public function voidIt():bool {
        // check if document wasn't completed
        // if so, no extra process needed
        if (!$this->wasCompleted())
            // not completed Order didn't do anything yet
            // we are safe to complete the voidIt process
            return true;

        // check if document is purchase
        // if so, no extra process needed
        if ($this->is_purchase)
            // purchase Orders don't do any on completeIt,
            // we are safe to complete the voidIt process
            return true;

        // if document is sale, we need to rollback reserved stock and void InOut document

        // check if Order is already invoiced
        if ($this->is_invoiced)
            // reject process, stock must return through a MaterialReturn document
            return $this->documentError('sales::order.voidIt.already-invoiced') == null;

        // check if Order hasn't InOut document
        // if so, all order lines contains non stockable products
        if (!$this->inOut)
            // we are safe to complete the voidIt process
            return true;

        // try to rollback InOut document
        if (!$this->inOut->processIt( self::ACTION_Void ))
            // redirect inOut document error
            return $this->documentError( $this->inOut->getDocumentError() ) === null;

        // revert reserved stock
        foreach ($this->lines as $line) {
            // ignore line if product.type isn't stockable
            if (!$line->product->stockable) continue;

            // reserved quantity
            $pendingToRevert = $line->quantity_ordered;

            // revert stock for Variant|Product on configured locators
            foreach (($line->variant ?? $line->product)->locators as $locator) {
                // check if locator belongs to current branch
                if ($locator->warehouse->branch_id !== $this->branch_id) continue;
                // check if storage has reserved stock
                if (($storage = Storage::getFromProductOnLocator($line->product, $line->variant, $locator))->reserved > 0) {
                    // calculate available reserved stock to revert on current storage
                    $availableToRevert = $storage->reserved > $pendingToRevert ? $pendingToRevert : $storage->reserved;
                    // revert reserved stock on storage
                    if (!$storage->update([ 'reserved' => $storage->reserved - $availableToRevert ]))
                        // return document error
                        return $this->documentError( $storage->errors()->first() );
                    // reduce pending quantity to revert
                    $pendingToRevert -= $availableToRevert;
                    // check if already reverted all pending quantity, if so exit loop
                    if ($pendingToRevert === 0) break;
                }
            }

            // revert reserved stock for Variant|Product on existing Storages
            foreach (Storage::getFromProduct($line->product, $line->variant, $this->branch) as $storage) {
                // check if storage has available reserved stock
                if ($storage->reserved > 0) {
                    // calculate available reserved stock to revert on current storage
                    $availableToRevert = $storage->reserved > $pendingToRevert ? $pendingToRevert : $storage->reserved;
                    // revert reserved stock on storage
                    if (!$storage->update([ 'reserved' => $storage->reserved + $availableToRevert ]))
                        // return document error
                        return $this->documentError( $storage->errors()->first() );
                    // reduce pending quantity to revert
                    $pendingToRevert -= $availableToRevert;
                    // check if already reverted all pending quantity, if so exit loop
                    if ($pendingToRevert === 0) break;
                }
            }

            // check if there is remaining reserved quantity to revert
            if ($pendingToRevert !== 0)
                // reject with error
                return $this->documentError('sales::order.voidIt.reserved-to-revert-on-storage', [
                    'product'   => $line->product->name,
                    'variant'   => $line->variant?->sku,
                ]);
        }

        // document voided
        return true;
    }

}
