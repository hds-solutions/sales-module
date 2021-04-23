<?php

namespace HDSSolutions\Finpar\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Route;

class SalesMenu {
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next) {
        // create a submenu
        $sub = backend()->menu()
            ->add(__('sales::sales.nav'), [
                'icon'  => 'cogs',
            ])->data('priority', 700);

        $this
            // append items to submenu
            ->index($sub);

        // continue witn next middleware
        return $next($request);
    }

    private function index(&$menu) {
        if (Route::has('backend.orders'))
            $menu->add(__('sales::orders.nav'), [
                'route'     => 'backend.orders',
                'icon'      => 'empties'
            ]);

        return $this;
    }

}
