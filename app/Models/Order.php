<?php

namespace HDSSolutions\Finpar\Models;

use HDSSolutions\Finpar\Interfaces\Document;
use HDSSolutions\Finpar\Traits\HasDocumentActions;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Validator;

class Order extends X_Order implements Document {
    use HasDocumentActions;

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

    public function partnerable() {
        return $this->morphTo(type: 'partnerable_type', id: 'partnerable_id');
    }

    public function address() {
        return $this->belongsTo(Address::class);
    }

    public function lines() {
        return $this->hasMany(OrderLine::class);
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
        // check if document age validations are enabled
        if (config('settings.validate-orders-age')) {
            // check drafted order from more than XX days ago
            if (self::drafted()->createdAgo( config('settings.pending-documents-age') )->count() > 0)
                // reject order with error
                return $validator->errors()->add([
                    'id'    => __('sales::orders.drafted-created-ago', [
                        'days'  => config('settings.pending-documents-age'),
                    ]);
                ]);

            // check if there is orders pending for invoiceIt from more than XX days ago
            if (self::invoiced(false)->createdAgo( config('settings.pending-documents-age') )->count() > 0)
                // reject order with error
                return $validator->errors()->add([
                    'id'    => __('sales::orders.not-invoiced-created-ago', [
                        'days'  => config('settings.pending-documents-age'),
                    ]);
                ]);
        }

        // TODO: set employee from session
        if (!$this->exists) $this->employee()->associate( auth()->user() );
    }

    public function prepareIt():?string {
        // check if document has lines
        if (!$this->lines()->count()) return $this->documentError('sales::order.no-lines');

        // line validations only for sale document
        if ($this->is_sale) foreach ($this->lines as $line) {
            // check if product is sold
            if (!$line->product->type->is_sold)
                // return document error
                return $this->documentError('sales::order.lines.product-isnt-sold', [
                    'product'   => $line->product->name,
                    'variant'   => $line->variant?->sku,
                ]);

            // check if there are drafted Inventories of Variant|Product
            if (Inventory::hasOpenForProduct( $line->product, $line->variant, $this->branch ))
                // reject line with error
                return $this->documentError('sales::order.lines.pending-inventories', [
                    'product'   => $line->product->name,
                    'variant'   => $line->variant?->sku,
                ]);

            // check available stock of Variant|Product
            if ($line->quantity_ordered > ($available = Storage::getQtyAvailable( $line->product, $line->variant, $this->branch )))
                // reject line with error
                return $this->documentError('sales::order.lines.no-enough-stock', [
                    'product'   => $line->product->name,
                    'variant'   => $line->variant?->sku,
                    'available' => $available,
                ]);
        }

        // return status InProgress
        return Document::STATUS_InProgress;
    }

    public function completeIt():?string {
        // reserve stock of Variants|Products (only when document is sale)
        if ($this->is_sale) foreach ($this->lines as $line) {
            // total quantity to reserve
            $pendingToReserve = $line->quantity_ordered;
            // get Variant|Product locators
            foreach (($line->variant ?? $line->product)->locators as $locator)
                // check if storage has available stock
                if (($storage = Storage::getFromProductOnLocator($line->product, $line->variant, $locator))->available > 0) {
                    // calculate available stock to reserver on current storage
                    $reserved = $storage->available > $pendingToReserve ? $pendingToReserve : $storage->available;
                    // reserve stock on storage
                    if (!$storage->update([ 'reserved' => $storage->reserved + $reserved ]))
                        // return document error
                        return $this->documentError( $storage->errors()->first() );
                    // reduce pending quantity to reserver
                    $pendingToReserve -= $reserved;
                    // if all pending quantity was already reserved, exit loop
                    if ($pendingToReserve === 0) break;
                }

            // if there is pending quantity, reject document process
            if ($pendingToReserve > 0) return $this->documentError('sales::order.lines.pending-to-reserve', [
                'product'   => $line->product->name,
                'variant'   => $line->variant?->sku,
            ]);
        }

        // create InOut document
        if (!($inOut = InOut::createFromOrder( $this ))->exists)
            // redirect inOut document error
            return $this->documentError( $inOut->getDocumentError() );

        // return completed status
        return Document::STATUS_Completed;
    }

}
