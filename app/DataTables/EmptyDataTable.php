<?php

namespace HDSSolutions\Finpar\DataTables;

use HDSSolutions\Finpar\Models\Empty as Resource;
use Yajra\DataTables\Html\Column;

class EmptyDataTable extends Base\DataTable {

    public function __construct() {
        parent::__construct(
            Resource::class,
            route('backend.empties'),
        );
    }

    protected function getColumns() {
        return [
            Column::computed('id')
                ->title( __('empty::empty.id.0') )
                ->hidden(),

            Column::make('name')
                ->title( __('empty::empty.name.0') ),

            Column::make('actions'),
        ];
    }

}
