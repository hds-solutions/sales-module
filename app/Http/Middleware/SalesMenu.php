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

            ->purchase_orders($sub)
            ->purchase_invoices($sub)
            ->purchase_receipments($sub)

            ->sale_orders($sub)
            ->sale_invoices($sub)
            ->sale_receipments($sub)

            ->reports($sub);

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

    private function purchase_orders(&$menu) {
        if (Route::has('backend.purchases.orders') && $this->can('orders.crud.index'))
            $menu->add(__('sales::orders.purchases.nav'), [
                'route'     => [ 'backend.purchases.orders' ],
                'icon'      => 'file-invoice'
            ]);

        return $this;
    }

    private function purchase_invoices(&$menu) {
        if (Route::has('backend.purchases.invoices') && $this->can('invoices.crud.index'))
            $menu->add(__('sales::invoices.purchases.nav'), [
                'route'     => [ 'backend.purchases.invoices' ],
                'icon'      => 'file-invoice-dollar'
            ]);

        return $this;
    }

    private function purchase_receipments(&$menu) {
        if (Route::has('backend.purchases.receipments') && $this->can('receipments.crud.index'))
            $menu->add(__('sales::receipments.purchases.nav'), [
                'route'     => 'backend.purchases.receipments',
                'icon'      => 'receipt'
            ]);

        return $this;
    }

    private function sale_orders(&$menu) {
        if (Route::has('backend.sales.orders') && $this->can('orders.crud.index'))
            $menu->add(__('sales::orders.sales.nav'), [
                'route'     => [ 'backend.sales.orders' ],
                'icon'      => 'file-invoice'
            ]);

        return $this;
    }

    private function sale_invoices(&$menu) {
        if (Route::has('backend.sales.invoices') && $this->can('invoices.crud.index'))
            $menu->add(__('sales::invoices.sales.nav'), [
                'route'     => [ 'backend.sales.invoices' ],
                'icon'      => 'file-invoice-dollar'
            ]);

        return $this;
    }

    private function sale_receipments(&$menu) {
        if (Route::has('backend.sales.receipments') && $this->can('receipments.crud.index'))
            $menu->add(__('sales::receipments.sales.nav'), [
                'route'     => 'backend.sales.receipments',
                'icon'      => 'receipt'
            ]);

        return $this;
    }

    private function reports(&$menu) {
        if (Route::has('backend.reports.sales.invoices') && $this->can('reports.sales.invoices'))
            $menu->add(__('sales::reports.sales.invoices.0'), [
                'route'     => 'backend.reports.sales.invoices',
                'icon'      => 'chart-line'
            ]);

        return $this;
    }

}
