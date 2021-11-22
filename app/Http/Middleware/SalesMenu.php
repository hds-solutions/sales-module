<?php

namespace HDSSolutions\Laravel\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Route;

class SalesMenu extends Base\Menu {

    public function handle($request, Closure $next) {
        // create a submenu
        $sub = backend()->menu()
            ->add(__('sales::sales.nav'), [
                'nickname'  => 'sales',
                'icon'      => 'chart-bar',
            ])->data('priority', 700);

        // get configs menu group
        $configs = backend()->menu()->get('configs');

        $this
            // append items to submenu
            ->stampings($configs)

            ->orders($sub)
            ->invoices($sub)
            ->receipments($sub)
            ;

        // continue witn next middleware
        return $next($request);
    }

    private function stampings(&$menu) {
        if (Route::has('backend.stampings') && $this->can('stampings.crud.index'))
            $menu->add(__('sales::stampings.nav'), [
                'route'     => 'backend.stampings',
                'icon'      => 'file-invoice'
            ]);

        return $this;
    }

    private function orders(&$menu) {
        if (Route::has('backend.orders') && $this->can('orders.crud.index'))
            $menu->add(__('sales::orders.nav'), [
                'route'     => 'backend.orders',
                'icon'      => 'file-invoice'
            ]);

        return $this;
    }

    private function invoices(&$menu) {
        if (Route::has('backend.invoices') && $this->can('invoices.crud.index'))
            $menu->add(__('sales::invoices.nav'), [
                'route'     => 'backend.invoices',
                'icon'      => 'file-invoice-dollar'
            ]);

        return $this;
    }

    private function receipments(&$menu) {
        if (Route::has('backend.receipments') && $this->can('receipments.crud.index'))
            $menu->add(__('sales::receipments.nav'), [
                'route'     => 'backend.receipments',
                'icon'      => 'receipt'
            ]);

        return $this;
    }

}
