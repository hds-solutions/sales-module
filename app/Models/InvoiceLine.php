<?php

namespace HDSSolutions\Laravel\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Validator;

class InvoiceLine extends X_InvoiceLine {

    public function __construct(array|OrderLine $attributes = []) {
        // check if is instance of OrderLine
        if (($orderLine = $attributes) instanceof OrderLine) $attributes = self::fromOrderLine($orderLine);
        // redirect attributes to parent
        parent::__construct(is_array($attributes) ? $attributes : []);
    }

    private static function fromOrderLine(OrderLine $orderLine):array {
        // copy attributes from OrderLine
        return [
            'currency_id'       => $orderLine->currency_id,
            'product_id'        => $orderLine->product_id,
            'variant_id'        => $orderLine->variant_id,
            'price_reference'   => $orderLine->price_reference,
            'price_ordered'     => $orderLine->price_ordered,
            'price_invoiced'    => $orderLine->price_ordered,
            'quantity_ordered'  => $orderLine->quantity_ordered,
            'quantity_invoiced' => $orderLine->quantity_ordered - $orderLine->quantity_invoiced,
        ];
    }

    public function invoice() {
        return $this->belongsTo(Invoice::class);
    }

    public function currency() {
        return $this->belongsTo(Currency::class)
            ->withTrashed();
    }

    public function employee() {
        return $this->belongsTo(Employee::class)
            ->withTrashed();
    }

    public function product() {
        return $this->belongsTo(Product::class)
            ->withTrashed();
    }

    public function variant() {
        return $this->belongsTo(Variant::class)
            ->withTrashed();
    }

    public function orderLines() {
        return $this->belongsToMany(OrderLine::class)
            ->using(InvoiceLineOrderLine::class)
            ->withPivot([ 'quantity_ordered', 'quantity_invoiced' ])
            ->withTimestamps();
    }

    public function beforeSave(Validator $validator) {
        // check if invoice already has a line with current Variant|Product
        if (!$this->exists && $this->invoice->hasProduct( $this->product, $this->variant ))
            // reject line with error
            return $validator->errors()->add('product_id', __('sales::invoice.lines.already-has-product', [
                'product'   => $this->product->name,
                'variant'   => $this->variant?->sku,
            ]));

        // copy currency from head if not set
        if (!$this->currency) $this->currency()->associate( $this->invoice->currency );
        // copy employee from head if not set
        if (!$this->employee) $this->employee()->associate( $this->invoice->employee );

        // set original price from product|variant
        if (!$this->exists) $this->price_reference =
            // set variant price if variant is set
            $this->variant?->price( $this->invoice->priceList )?->price?->price ??
            // otherwise, set product price without variant
            $this->product?->price( $this->invoice->priceList )?->price?->price ?? 0;

        // calculate line total amount
        $this->total = $this->price_invoiced * $this->quantity_invoiced;

        // update isInvoiced status
        $this->is_invoiced = $this->quantity_ordered == $this->quantity_invoiced;
    }

    public function afterSave() {
        // update invoice total amount
        $this->invoice->update([ 'total' => $this->invoice->lines()->sum('total') ]);
    }

}
