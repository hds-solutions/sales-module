<?php

use Illuminate\Support\Facades\Route;
use HDSSolutions\Laravel\Http\Controllers\{
    StampingController,

    PurchaseOrderController,
    PurchaseInvoiceController,
    PurchaseReceipmentController,

    SaleOrderController,
    SaleInvoiceController,
    SaleReceipmentController,
    SalesController,

    SalesReportsController,
};

Route::group([
    'prefix'        => config('backend.prefix'),
    'middleware'    => [ 'web', 'auth:'.config('backend.guard') ],
], function() {
    // name prefix
    $name_prefix = [ 'as' => 'backend' ];

    Route::resource('stampings',                StampingController::class,  $name_prefix)
        ->parameters([ 'stampings' => 'resource' ])
        ->name('index', 'backend.stampings');


    Route::resource('purchases/orders',                     PurchaseOrderController::class, [ 'as' => 'backend.purchases' ])
        ->parameters([ 'orders' => 'resource' ])
        ->name('index', 'backend.purchases.orders');
    Route::post('purchases/orders/{resource}/process',      [ PurchaseOrderController::class, 'processIt' ])
        ->name('backend.purchases.orders.process');

    Route::resource('purchases/invoices',                   PurchaseInvoiceController::class, [ 'as' => 'backend.purchases' ])
        ->parameters([ 'invoices' => 'resource' ])
        ->name('index', 'backend.purchases.invoices');
    Route::get('purchases/invoices/{resource}/print.pdf',   [ PurchaseInvoiceController::class, 'printIt' ])
        ->name('backend.purchases.invoices.print');
    Route::post('purchases/invoices/{resource}/process',    [ PurchaseInvoiceController::class, 'processIt' ])
        ->name('backend.purchases.invoices.process');

    Route::resource('purchases/receipments',                 PurchaseReceipmentController::class, [ 'as' => 'backend.purchases' ])
        ->parameters([ 'receipments' => 'resource' ])
        ->name('index', 'backend.purchases.receipments')
        ->only([ 'index', 'show' ]);
    Route::post('purchases/receipments/{resource}/process',  [ PurchaseReceipmentController::class, 'processIt' ])
        ->name('backend.purchases.receipments.process');


    Route::resource('sales/orders',                     SaleOrderController::class, [ 'as' => 'backend.sales' ])
        ->parameters([ 'orders' => 'resource' ])
        ->name('index', 'backend.sales.orders');
    Route::post('sales/orders/{resource}/process',      [ SaleOrderController::class, 'processIt' ])
        ->name('backend.sales.orders.process');

    Route::resource('sales/invoices',                   SaleInvoiceController::class, [ 'as' => 'backend.sales' ])
        ->parameters([ 'invoices' => 'resource' ])
        ->name('index', 'backend.sales.invoices');
    Route::get('sales/invoices/{resource}/print.pdf',   [ SaleInvoiceController::class, 'printIt' ])
        ->name('backend.sales.invoices.print');
    Route::post('sales/invoices/{resource}/process',    [ SaleInvoiceController::class, 'processIt' ])
        ->name('backend.sales.invoices.process');

    Route::resource('sales/receipments',                SaleReceipmentController::class, [ 'as' => 'backend.sales' ])
        ->parameters([ 'receipments' => 'resource' ])
        ->name('index', 'backend.sales.receipments')
        ->only([ 'index', 'show' ]);
    Route::post('sales/receipments/{resource}/process', [ SaleReceipmentController::class, 'processIt' ])
        ->name('backend.sales.receipments.process');


    Route::post('sales/product',                [ SalesController::class, 'product' ])
        ->name('backend.sales.product');
    Route::post('sales/price',                  [ SalesController::class, 'price' ])
        ->name('backend.sales.price');


    Route::get('reports/sales/invoices',        [ SalesReportsController::class, 'sale_invoices' ], $name_prefix)
        ->name('backend.reports.sales.invoices');

});
