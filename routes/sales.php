<?php

use Illuminate\Support\Facades\Route;
use HDSSolutions\Finpar\Http\Controllers\{
    OrderController,
    InvoiceController,
    InOutController,
    ReceipmentController,
    PriceChangeController,
};

Route::group([
    'prefix'        => config('backend.prefix'),
    'middleware'    => [ 'web', 'auth:'.config('backend.guard') ],
], function() {
    // name prefix
    $name_prefix = [ 'as' => 'backend' ];

    Route::resource('orders',                   OrderController::class,     $name_prefix)
        ->parameters([ 'orders' => 'resource' ])
        ->name('index', 'backend.orders');
    Route::post('orders/price',                 [ OrderController::class, 'price' ])
        ->name('backend.orders.price');
    Route::post('orders/{resource}/process',    [ OrderController::class, 'processIt'])
        ->name('backend.orders.process');

    Route::resource('invoices',                 InvoiceController::class,   $name_prefix)
        ->parameters([ 'invoices' => 'resource' ])
        ->name('index', 'backend.invoices');
    Route::post('invoices/{resource}/process',  [ InvoiceController::class, 'processIt'])
        ->name('backend.invoices.process');

    Route::resource('in_outs',                  InOutController::class,   $name_prefix)
        ->parameters([ 'in_outs' => 'resource' ])
        ->name('index', 'backend.in_outs');
    Route::post('in_outs/{resource}/process',   [ InOutController::class, 'processIt'])
        ->name('backend.in_outs.process');

    Route::resource('receipments',                  ReceipmentController::class,   $name_prefix)
        ->parameters([ 'receipments' => 'resource' ])
        ->name('index', 'backend.receipments');
    Route::post('receipments/{resource}/process',   [ ReceipmentController::class, 'processIt'])
        ->name('backend.receipments.process');

});
