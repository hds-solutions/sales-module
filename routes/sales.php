<?php

use Illuminate\Support\Facades\Route;
use HDSSolutions\Finpar\Http\Controllers\
    {OrderController};

Route::group([
    'prefix'        => config('backend.prefix'),
    'middleware'    => [ 'web', 'auth:'.config('backend.guard') ],
], function() {
    // name prefix
    $name_prefix = [ 'as' => 'backend' ];

     Route::resource('orders',    OrderController::class,   $name_prefix)
         ->parameters([ 'orders' => 'resource' ])
         ->name('index', 'backend.orders');

});
