<?php

namespace HDSSolutions\Finpar;

use HDSSolutions\Laravel\Modules\ModuleServiceProvider;

class EmptyModuleServiceProvider extends ModuleServiceProvider {

    protected array $middlewares = [
        \HDSSolutions\Finpar\Http\Middleware\EmptyMenu::class,
    ];

    private $commands = [
        // \HDSSolutions\Finpar\Commands\SomeCommand::class,
    ];

    public function bootEnv():void {
        // enable config override
        $this->publishes([
            module_path('config/empty.php') => config_path('empty.php'),
        ], 'empty.config');

        // load routes
        $this->loadRoutesFrom( module_path('routes/empty.php') );
        // load views
        $this->loadViewsFrom( module_path('resources/views'), 'empty' );
        // load translations
        $this->loadTranslationsFrom( module_path('resources/lang'), 'empty' );
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
        app()->singleton(Empty::class, fn() => new Empty);
        // register commands
        $this->commands( $this->commands );
        // merge configuration
        $this->mergeConfigFrom( module_path('config/empty.php'), 'empty' );
    }

}
