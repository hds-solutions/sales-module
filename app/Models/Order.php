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

    public static function nextDocumentNumber():string {
        // return next document number for specified stamping
        return str_increment(self::max('document_number') ?? null);
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

    public function address() {
        return $this->belongsTo(Address::class);
    }

    public function lines() {
        return $this->hasMany(OrderLine::class);
    }

    public function scopeInvoiced(Builder $query, bool $invoiced = true) {
        return $query->where('is_invoiced', $invoiced);
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
                return $validator->errors()->add([
                    'id'    => __('sales::orders.drafted-created-ago', [
                        'days'  => config('settings.pending-documents-age'),
                    ])
                ]);

            // check if there is orders pending for invoiceIt from more than XX days ago
            if (self::invoiced(false)->createdAgo( config('settings.pending-documents-age') )->count() > 0)
                // reject order with error
                return $validator->errors()->add([
                    'id'    => __('sales::orders.not-invoiced-created-ago', [
                        'days'  => config('settings.pending-documents-age'),
                    ])
                ]);
        }

        // total must have value
        $this->total = $this->total ?? 0;
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

            // validations for products that has stock
            if ($line->product->stockable) {
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
        }

        // return status InProgress
        return Document::STATUS_InProgress;
    }

    public function completeIt():?string {
        // check if document is sale
        // if so, reserve stock and create InOut document
        if ($this->is_sale) {
            // reserve stock of Variants|Products (only when document is sale)
            foreach ($this->lines as $line) {
                // ignore line if product.type isn't stockable
                if (!$line->product->stockable) continue;

                // total quantity to reserve
                $pendingToReserve = $line->quantity_ordered;

                // reserve stock for Variant|Product on configured locators
                foreach (($line->variant ?? $line->product)->locators as $locator) {
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
                }

                // reserve stock for Variant|Product on existing Storages
                foreach (Storage::getFromProduct($line->product, $line->variant, $this->branch) as $storage) {
                    // check if storage has available stock
                    if ($storage->available > 0) {
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
                }

                // if there is pending quantity, reject document process
                if ($pendingToReserve !== 0)
                    // reject with error
                    return $this->documentError('sales::order.lines.pending-to-reserve-failed', [
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
        }

        // return completed status
        return Document::STATUS_Completed;
    }

}
