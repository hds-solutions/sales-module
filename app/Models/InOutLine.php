<?php

namespace HDSSolutions\Finpar\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Validator;

class InOutLine extends X_InOutLine {

    public function inOut() {
        return $this->belongsTo(InOut::class);
    }

    public function orderLine() {
        return $this->belongsTo(OrderLine::class);
    }

    public function product() {
        return $this->belongsTo(Product::class);
    }

    public function variant() {
        return $this->belongsTo(Variant::class);
    }

    public function locator() {
        return $this->belongsTo(Locator::class);
    }

    public function beforeSave(Validator $validator) {
        // check if product is stockable
        if (!$this->product->stockable)
            // reject line with error
            return $validator->errors()->add([
                'product_id'    => __('sales::in_out.lines.product-not-stockable', [
                    'product'   => $this->product->name,
                    'variant'   => $this->variant?->sku,
                ])
            ]);

        // check if InOut already has a line with current Variant|Product
        if ($this->inOut->hasProduct( $this->product, $this->variant ))
            // reject line with error
            return $validator->errors()->add([
                'product_id'    => __('sales::in_out.lines.already-has-product', [
                    'product'   => $this->product->name,
                    'variant'   => $this->variant?->sku,
                ])
            ]);

        // check if there are drafted Inventories of Variant|Product
        if (Inventory::hasOpenForProduct( $this->product, $this->variant, $this->inOut->branch ))
            // reject line with error
            return $validator->errors()->add([
                'product_id'    => __('sales::in_out.lines.pending-inventories', [
                    'product'   => $this->product->name,
                    'variant'   => $this->variant?->sku,
                ])
            ]);
    }

}
