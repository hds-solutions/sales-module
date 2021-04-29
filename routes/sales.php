<?php

use Illuminate\Support\Facades\Route;
use HDSSolutions\Finpar\Http\Controllers\{
    OrderController,
    PriceChangeController,
};

Route::group([
    'prefix'        => config('backend.prefix'),
    'middleware'    => [ 'web', 'auth:'.config('backend.guard') ],
], function() {
    // name prefix
    $name_prefix = [ 'as' => 'backend' ];

    Route::resource('orders',   OrderController::class, $name_prefix)
        ->parameters([ 'orders' => 'resource' ])
        ->name('index', 'backend.orders');

    Route::post('orders/price',                 [ PriceChangeController::class, 'price' ])
        ->name('backend.orders.price');
    Route::post('orders/{resource}/process',    [ OrderController::class, 'processIt'])
        ->name('backend.orders.process');

});
