<?php

namespace HDSSolutions\Laravel;

use HDSSolutions\Laravel\Models\Invoice;
use HDSSolutions\Laravel\Modules\ModuleServiceProvider;
use Illuminate\Foundation\AliasLoader;

class SalesModuleServiceProvider extends ModuleServiceProvider {

    protected array $middlewares = [
        \HDSSolutions\Laravel\Http\Middleware\SalesMenu::class,
    ];

    private $commands = [
        // \HDSSolutions\Laravel\Commands\SomeCommand::class,
    ];

    public function bootEnv():void {
        // enable config override
        $this->publishes([
            module_path('config/sales.php') => config_path('sales.php'),
        ], 'sales.config');

        // load routes
        $this->loadRoutesFrom( module_path('routes/sales.php') );
        // load views
        $this->loadViewsFrom( module_path('resources/views'), 'sales' );
        // load translations
        $this->loadTranslationsFrom( module_path('resources/lang'), 'sales' );
        // load migrations
        $this->loadMigrationsFrom( module_path('database/migrations') );
        // load seeders
        $this->loadSeedersFrom( module_path('database/seeders') );
    }

    public function register() {
        // register helpers
        if (file_exists($helpers = realpath(__DIR__.'/helpers.php')))
            //
            require_once $helpers;
        // register singleton
        app()->singleton(Sales::class, fn() => new Sales);
        // register commands
        $this->commands( $this->commands );
        // merge configuration
        $this->mergeConfigFrom( module_path('config/sales.php'), 'sales' );
        //
        $this->alias('Invoice', Invoice::class);
    }

}
