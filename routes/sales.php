<?php

use Illuminate\Support\Facades\Route;

Route::group([
    'prefix'        => config('backend.prefix'),
    'middleware'    => [ 'web', 'auth:'.config('backend.guard') ],
], function() {
    // name prefix
    $name_prefix = [ 'as' => 'backend' ];

    // Route::resource('empties',    EmptyController::class,   $name_prefix)
    //     ->parameters([ 'empties' => 'resource' ])
    //     ->name('index', 'backend.empties');

});
