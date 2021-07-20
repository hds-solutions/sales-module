<?php

namespace HDSSolutions\Laravel\Models;

use Illuminate\Database\Eloquent\Builder;

abstract class X_InvoiceLine extends Base\Model {

    protected $fillable = [
        'invoice_id',
        'currency_id',
        'product_id',
        'variant_id',
        'price_reference',
        'price_ordered',
        'price_invoiced',
        'quantity_ordered',
        'quantity_invoiced',
        'quantity_received',
        'total',
        'conversion_rate',
    ];

    protected static array $rules = [
        'invoice_id'        => [ 'required' ],
        'currency_id'       => [ 'required' ],
        'product_id'        => [ 'required' ],
        'variant_id'        => [ 'sometimes', 'nullable' ],
        'price_reference'   => [ 'sometimes', 'nullable', 'min:0' ],
        'price_ordered'     => [ 'sometimes', 'nullable', 'min:0' ],
        'price_invoiced'    => [ 'required', 'min:0' ],
        'quantity_ordered'  => [ 'sometimes', 'nullable', 'min:0' ],
        'quantity_invoiced' => [ 'required', 'min:0' ],
        'quantity_received' => [ 'sometimes', 'nullable', 'min:0' ],
        'total'             => [ 'sometimes' ],
        'conversion_rate'   => [ 'sometimes', 'nullable', 'min:0' ],
    ];

    public function getPriceReferenceAttribute():int|float {
        return $this->attributes['price_reference'] / pow(10, currency($this->currency_id)->decimals);
    }

    public function setPriceReferenceAttribute(int|float $price_reference) {
        $this->attributes['price_reference'] = $price_reference * pow(10, currency($this->currency_id)->decimals);
    }

    public function getPriceOrderedAttribute():int|float {
        return $this->attributes['price_ordered'] / pow(10, currency($this->currency_id)->decimals);
    }

    public function setPriceOrderedAttribute(int|float $price_ordered) {
        $this->attributes['price_ordered'] = $price_ordered * pow(10, currency($this->currency_id)->decimals);
    }

    public function getPriceInvoicedAttribute():int|float {
        return $this->attributes['price_invoiced'] / pow(10, currency($this->currency_id)->decimals);
    }

    public function setPriceInvoicedAttribute(int|float $price_invoiced) {
        $this->attributes['price_invoiced'] = $price_invoiced * pow(10, currency($this->currency_id)->decimals);
    }

    public function getTotalAttribute():int|float {
        return $this->attributes['total'] / pow(10, currency($this->currency_id)->decimals);
    }

    public function setTotalAttribute(int|float $total) {
        $this->attributes['total'] = $total * pow(10, currency($this->currency_id)->decimals);
    }

}
