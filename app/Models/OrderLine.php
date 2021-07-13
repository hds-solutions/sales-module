<?php

namespace HDSSolutions\Finpar\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Validator;

class OrderLine extends X_OrderLine {

    public function order() {
        return $this->belongsTo(Order::class);
    }

    public function currency() {
        return $this->belongsTo(Currency::class);
    }

    public function employee() {
        return $this->belongsTo(Employee::class);
    }

    public function product() {
        return $this->belongsTo(Product::class);
    }

    public function variant() {
        return $this->belongsTo(Variant::class);
    }

    public function invoiceLines() {
        return $this->belongsToMany(InvoiceLine::class)
            ->using(InvoiceLineOrderLine::class)
            ->withPivot([ 'quantity_ordered', 'quantity_invoiced' ])
            ->withTimestamps();
    }

    public function scopeInvoiced(Builder $query, bool $invoiced = true) {
        return $query->where('is_invoiced', $invoiced);
    }

    public function beforeSave(Validator $validator) {
        // check if order already has a line with current Variant|Product
        if (!$this->exists && $this->order->hasProduct( $this->product, $this->variant ))
            // reject line with error
            return $validator->errors()->add('product_id', __('sales::order.lines.already-has-product', [
                'product'   => $this->product->name,
                'variant'   => $this->variant?->sku,
            ]));

        // validations when product is stockable
        if ($this->order->isOpen() && $this->product->stockable) {
            // check if there are drafted Inventories of Variant|Product
            if (Inventory::hasOpenForProduct( $this->product, $this->variant, $this->order->branch ))
                // reject line with error
                return $validator->errors()->add('product_id', __('sales::order.lines.pending-inventories', [
                    'product'   => $this->product->name,
                    'variant'   => $this->variant?->sku,
                ]));

            // check available stock of Variant|Product
            if ($this->quantity_ordered > ($available = Storage::getQtyAvailable( $this->product, $this->variant, $this->order->branch )))
                // reject line with error
                return $validator->errors()->add('product_id', __('sales::order.lines.no-enough-stock', [
                    'product'   => $this->product->name,
                    'variant'   => $this->variant?->sku,
                    'available' => $available,
                ]));
        }

        // copy currency from head if not set
        if (!$this->currency) $this->currency()->associate( $this->order->currency );
        // copy employee from head if not set
        if (!$this->employee) $this->employee()->associate( $this->order->employee );

        // set original price from product|variant
        if (!$this->exists) $this->price_reference =
            // set variant price if variant is set
            $this->variant?->price($this->currency)?->pivot?->price ??
            // otherwise, set product price without variant
            $this->product?->price($this->currency)?->pivot?->price ?? 0;

        // calculate line total amount
        $this->total = $this->price_ordered * $this->quantity_ordered;
    }

    public function afterSave() {
        $this->order->update([
            // update Order.total amount
            'total'         => $this->order->lines()->sum('total'),
            // update Order.is_invoiced flag
            'is_invoiced'   => $this->order->lines()->invoiced(false)->count() === 0,
        ]);
    }

}
