<?php

namespace HDSSolutions\Laravel\Http\Controllers;

use App\Http\Controllers\Controller;
use HDSSolutions\Laravel\Http\Request;
use HDSSolutions\Laravel\Models\Currency;
use HDSSolutions\Laravel\Models\Product;
use HDSSolutions\Laravel\Models\Variant;

class SalesController extends Controller {

    public function product(Request $request) {
        // find product/variant
        $product = Product::code( $request->product );
        $variant = Variant::sku( $request->product );
        $product ??= $variant?->product;
        //
        $currency = $request->has('currency') ? Currency::findOrFail($request->currency) : pos_settings()->currency();
        //
        return response()->json([
            'product'   => $product ?? null,
            'variant'   => $variant ?? null,
            'image'     => $variant?->images->first()->url ?? $product?->images->first()->url ?? asset('backend-module/assets/images/default.jpg'),
            'price'     => $variant?->price( $currency )?->pivot->price ?? $product?->price( $currency )?->pivot->price ?? null,
        ]);
    }

    public function price(Request $request) {
        // get resources
        $product = $request->has('product') ? Product::findOrFail($request->product) : null;
        $variant = $request->has('variant') ? Variant::findOrFail($request->variant) : null;
        $currency = $request->has('currency') ? Currency::findOrFail($request->currency) : null;
        // return stock for requested product
        return response()->json($variant?->price($currency)?->pivot ?? $product?->price($currency)?->pivot);
    }

}
