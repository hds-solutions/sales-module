<?php

namespace HDSSolutions\Laravel\DataTables;

use HDSSolutions\Laravel\Models\Stamping as Resource;
use Yajra\DataTables\Html\Column;

class StampingDataTable extends Base\DataTable {

    protected array $withCount = [
        'invoices',
    ];

    protected array $orderBy = [
        'valid_until'   => 'desc',
        'valid_from'    => 'desc',
    ];

    public function __construct() {
        parent::__construct(
            Resource::class,
            route('backend.stampings'),
        );
    }

    protected function getColumns() {
        return [
            Column::computed('id')
                ->title( __('sales::stamping.id.0') )
                ->hidden(),

            Column::make('stamping')
                ->title( __('sales::stamping.stamping.0') ),

            Column::computed('invoices')
                ->title( __('sales::stamping.invoices.0') )
                ->renderRaw('view:stamping')
                ->data( view('sales::stampings.datatable.invoices')->render() )
                ->class('w-150px'),

            Column::computed('actions'),
        ];
    }

}
