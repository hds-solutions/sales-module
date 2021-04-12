<?php

namespace HDSSolutions\Finpar\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Route;

class EmptyMenu {
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
            ->add(__('empty::empties.nav'), [
                'icon'  => 'cogs',
            ])->data('priority', 700);

        $this
            // append items to submenu
            ->empties($sub);

        // continue witn next middleware
        return $next($request);
    }

    private function empties(&$menu) {
        if (Route::has('backend.empties'))
            $menu->add(__('empty::empties.nav'), [
                'route'     => 'backend.empties',
                'icon'      => 'empties'
            ]);

        return $this;
    }

}
