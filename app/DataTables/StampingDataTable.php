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

            Column::make('document_number')
                ->title( __('sales::stamping.document_number.0') ),

            Column::make('valid_from_pretty')
                ->title( __('sales::stamping.valid_from.0') ),

            Column::make('valid_until_pretty')
                ->title( __('sales::stamping.valid_until.0') ),

            Column::make('current')
                ->title( __('sales::stamping.current.0') ),

            Column::computed('invoices')
                ->title( __('sales::stamping.invoices.0') )
                ->renderRaw('view:stamping')
                ->data( view('sales::stampings.datatable.invoices')->render() )
                ->class('w-150px'),

            Column::computed('actions'),
        ];
    }

}
