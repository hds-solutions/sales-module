<?php

use Illuminate\Support\Facades\Route;
use HDSSolutions\Laravel\Http\Controllers\{
    OrderController,
    InvoiceController,
    SalesController,
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
    Route::post('orders/{resource}/process',    [ OrderController::class, 'processIt'])
        ->name('backend.orders.process');

    Route::resource('invoices',                 InvoiceController::class,   $name_prefix)
        ->parameters([ 'invoices' => 'resource' ])
        ->name('index', 'backend.invoices');
    Route::post('invoices/{resource}/process',  [ InvoiceController::class, 'processIt'])
        ->name('backend.invoices.process');

    Route::post('sales/product',                [ SalesController::class, 'product' ])
        ->name('backend.sales.product');
    Route::post('sales/price',                  [ SalesController::class, 'price' ])
        ->name('backend.sales.price');

    Route::resource('receipments',                  ReceipmentController::class,   $name_prefix)
        ->parameters([ 'receipments' => 'resource' ])
        ->name('index', 'backend.receipments');
        // ->only([ 'index', 'show' ]);
    Route::post('receipments/{resource}/process',   [ ReceipmentController::class, 'processIt'])
        ->name('backend.receipments.process');

});
