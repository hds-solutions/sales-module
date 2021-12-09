<?php

namespace HDSSolutions\Laravel\Http\Controllers;

use App\Http\Controllers\Controller;
use HDSSolutions\Laravel\Http\Request;
use HDSSolutions\Laravel\Models\PriceList;
use HDSSolutions\Laravel\Models\Product;
use HDSSolutions\Laravel\Models\Variant;

class SalesController extends Controller {

    public function product(Request $request) {
        // find product/variant
        $product = Product::code( $request->product );
        $variant = Variant::sku( $request->product );
        // load product from variant
        $product ??= $variant?->product;

        // return product info
        return response()->json([
            'product'   => $product ?? null,
            'variant'   => $variant ?? null,
            'image'     => $variant?->images->first()->url ?? $product?->images->first()->url ?? asset('backend-module/assets/images/default.jpg'),
        ]);
    }

    public function price(Request $request) {
        // check invalid params and return invalid request messaje
        if (!$request->has('product')) return response()->json([ 'error' => 'Invalid request' ]);

        // load resources
        $product = $request->has('product') ? Product::findOrFail($request->product) : null;
        $variant = $request->has('variant') ? Variant::findOrFail($request->variant) : null;
        $priceList = $request->has('priceList') ? PriceList::findOrFail($request->priceList) : null;

        // get price
        $price = $priceList ? (
            $variant?->price( $priceList )?->price?->price ??
            $product?->price( $priceList )?->price?->price
        ) : null;

        // return price for requested variant/product
        return response()->json([
            'price'     => $price,
            'formatted' => $price ? number($price, currency($priceList->currency_id)->decimals) : null,
        ]);
    }

}
