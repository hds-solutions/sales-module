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
                'nickname'  => 'sales',
                'icon'      => 'cogs',
            ])->data('priority', 700);

        $this
            // append items to submenu
            ->orders($sub)
            ->invoices($sub)
            ->in_outs($sub)
            ->receipments($sub)
            ;

        // continue witn next middleware
        return $next($request);
    }

    private function orders(&$menu) {
        if (Route::has('backend.orders'))
            $menu->add(__('sales::orders.nav'), [
                'route'     => 'backend.orders',
                'icon'      => 'orders'
            ]);

        return $this;
    }

    private function invoices(&$menu) {
        if (Route::has('backend.invoices'))
            $menu->add(__('sales::invoices.nav'), [
                'route'     => 'backend.invoices',
                'icon'      => 'invoices'
            ]);

        return $this;
    }

    private function in_outs(&$menu) {
        if (Route::has('backend.in_outs'))
            $menu->add(__('sales::in_outs.nav'), [
                'route'     => 'backend.in_outs',
                'icon'      => 'in_outs'
            ]);

        return $this;
    }

    private function receipments(&$menu) {
        if (Route::has('backend.receipments'))
            $menu->add(__('sales::receipments.nav'), [
                'route'     => 'backend.receipments',
                'icon'      => 'receipments'
            ]);

        return $this;
    }

}
