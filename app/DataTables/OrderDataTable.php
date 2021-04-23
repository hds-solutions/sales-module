<?php

namespace HDSSolutions\Finpar\DataTables;

use HDSSolutions\Finpar\Models\Order as Resource;
use Yajra\DataTables\Html\Column;

class OrderDataTable extends Base\DataTable {

    public function __construct() {
        parent::__construct(
            Resource::class,
            route('backend.orders'),
        );
    }

    protected function getColumns() {
        return [
            Column::computed('id')
                ->title( __('sales::order.id.0') )
                ->hidden(),

            Column::make('total')
                ->title( __('sales::order.total.0') ),

            Column::make('actions'),
        ];
    }

}
