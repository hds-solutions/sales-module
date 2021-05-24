<?php

namespace HDSSolutions\Finpar\Models;

use HDSSolutions\Finpar\Interfaces\Document;
use HDSSolutions\Finpar\Traits\HasDocumentActions;
use HDSSolutions\Finpar\Traits\HasPartnerable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Validator;

class InOut extends X_InOut implements Document {
    use HasDocumentActions,
        HasPartnerable;

    public function branch() {
        return $this->belongsTo(Branch::class);
    }

    public function warehouse() {
        return $this->belongsTo(Warehouse::class);
    }

    public function employee() {
        return $this->belongsTo(Employee::class);
    }

    public function invoice() {
        return $this->belongsTo(Invoice::class);
    }

    public function lines() {
        return $this->hasMany(InOutLine::class);
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
        if (!$this->exists && $this->employee_id === null) $this->employee()->associate( auth()->user() );

        // if document is material return and invoice not set
        if ($this->is_material_return && $this->invoice === null)
            // reject it, Invoice must be specified when returning
            $validator->errors()->add('invoice_id', __('sales::inout.material-return-invoice'));

        // check if new record and no document number is set
        if (!$this->exists && !$this->document_number)
            // set document number incrementing by 10
            $this->document_number = (int)self::max('document_number') + 10;
    }

    public function prepareIt():?string {
        // validations where is_material_return
        if ($this->is_material_return &&
            // InOut of Order must be completed in order to return items
            self::ofOrder( $this->order )->completed()->count() == 0)

            // return process error
            return $this->documentError('sales::in_out.order-not-completed', [
                'order' => $this->order,
            ]);

        // isSale=true and
        if ($this->is_sale && $this->is_material_return) foreach ($this->lines as $line) {
            // TODO: if is_material_return=true && line.quantity_movement == 0, reject since can't return empty lines
        }

        // return status InProgress
        return Document::STATUS_InProgress;
    }

    public function completeIt():?string {
        // process lines, updating stock based on document type
        foreach ($this->lines as $line) {
            // TODO: if is_material_return=true
                // TODO: check if returned quantity is greater than invoiced quantity and reject it

            // save total quantity to move
            $quantityToMove = $line->quantity_movement;
            // get Variant|Product locators
            foreach (($line->variant ?? $line->product)->locators as $locator) {
                // ignore storage if hasn't available stock
                if (($storage = Storage::getFromProductOnLocator($line->product, $line->variant, $locator))->available == 0) continue;

                // save available stock on current storage
                $availableOnStorage = $storage->available;

                // if document isSale, substract stock from Storage
                if ($this->isSale) {
                    // update stock on storage
                    $storage->fill([
                        // substract available from storage.onHand
                        'on_hand'   => $storage->on_hand - $availableOnStorage,
                        // substract available from storage.reserved
                        'reserved'  => $storage->reserved - $availableOnStorage,
                    ]);

                    // substract available from total quantity to move
                    $quantityToMove -= $availableOnStorage;
                }

                // if document isPurchase, add available stock on Storage
                if ($this->isPurchase) {
                    // update stock on storage
                    $storage->fill([
                        // add movement quantity to storage.onHand
                        'on_hand'   => $storage->on_hand + $quantityToMove,
                        // substract movement quantity from storage.pending
                        'pending'   => $storage->pending - $quantityToMove,
                    ]);

                    // set quantity to move to 0 (zero), all movement when to first location found
                    $quantityToMove = 0;
                }

                // if document is_material_return, add available stock on Storage
                if ($this->is_material_return) {
                    // update stock on storage
                    $storage->fill([
                        // add movement quantity to storage.onHand
                        'on_hand'   => $storage->on_hand + $quantityToMove,
                    ]);

                    // set quantity to move to 0 (zero), all movement when to first location found
                    $quantityToMove = 0;
                }

                // save storage changes
                if (!$storage->save())
                    // return document error
                    return $this->documentError( $storage->errors()->first() );

                // check if all movement quantity was already moved and exit loop
                if ($quantityToMove == 0) break;
            }

            // if not all movement quantity can be moved, reject process
            if ($quantityToMove > 0)
                // return document error
                return $this->documentError('sales::in_out.lines.no-storage-found', [
                    'product'   => $line->product->name,
                    'variant'   => $line->variant?->sku,
                ]);
        }

        // if document is_material_return, create a CreditNote for the returning amount
        if ($this->is_material_return) {
            // TODO: create CreditNote
        }

        // return completed status
        return Document::STATUS_Completed;
    }

    public function scopeOfOrder(Builder $query, int|Order $order) {
        // return InOut's from order
        return $query->where('order_id', $order instanceof Order ? $order->id : $order);
    }

    public static function createFromOrder(Order $order):self {
        // create new document
        $inOut = new self([
            'branch_id'         => $order->branch_id,
            'warehouse_id'      => $order->warehouse_id,
            'employee_id'       => $order->employee_id,
            'partnerable_type'  => $order->partnerable_type,
            'partnerable_id'    => $order->partnerable_id,
            'transacted_at'     => $order->transacted_at,
            'is_purchase'       => $order->is_purchase,
        ]);
        // save header
        if (!$inOut->save())
            // save error message and return instance
            return tap($inOut, fn($inOut) => $inOut->documentError( $inOut->errors()->first() ));

        // copy Order lines to InOut
        foreach ($order->lines as $orderLine) {
            // create new InOutLine
            $inOutLine = $inOut->lines()->make([
                'order_line_id'     => $orderLine->id,
                'product_id'        => $orderLine->product_id,
                'variant_id'        => $orderLine->variant_id,
                'quantity_ordered'  => $orderLine->quantity_ordered,
                'quantity_movement' => $orderLine->quantity_ordered,
            ]);
            // set first locator of Product|Variant
            $inOutLine->locator()->associate( ($orderLine->variant ?? $orderLine->product)->locators()->first() );
            // save line
            if (!$inOutLine->save()) {
                // save error message and return instance
                $inOut->documentError( $inOutLine->errors()->first() );
                return $inOut;
            }
        }

        // return created document
        return $inOut;
    }

}
