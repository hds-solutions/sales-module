<?php

namespace HDSSolutions\Laravel\Seeders;

class SalesPermissionsSeeder extends Base\PermissionsSeeder {

    public function __construct() {
        parent::__construct('sales');
    }

    protected function permissions():array {
        return [
            $this->resource('orders'),
            $this->document('orders'),
            $this->resource('invoices'),
            $this->document('invoices'),
            $this->resource('receipments'),
            $this->document('receipments'),
        ];
    }

    protected function afterRun():void {
        // create Casher role
        $this->role('Cashier', [
            'orders.*',
            'invoices.*',
            'receipments.*',
        ]);
    }

}
