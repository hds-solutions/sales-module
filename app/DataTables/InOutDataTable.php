<?php

namespace HDSSolutions\Finpar\DataTables;

use HDSSolutions\Finpar\Models\InOut as Resource;
use Yajra\DataTables\Html\Column;

class InOutDataTable extends Base\DataTable {

    protected array $with = [
        'partnerable',
    ];

    public function __construct() {
        parent::__construct(
            Resource::class,
            route('backend.in_outs'),
        );
    }

    protected function getColumns() {
        return [
            Column::computed('id')
                ->title( __('sales::in_out.id.0') )
                ->hidden(),

            Column::make('document_number')
                ->title( __('sales::in_out.document_number.0') ),

            Column::computed('transacted_at')
                ->title( __('sales::in_out.transacted_at.0') )
                ->renderRaw('datetime:transacted_at;F j, Y H:i'),

            Column::make('partnerable.full_name')
                ->title( __('sales::in_out.partnerable_id.0') ),

            Column::make('document_status_pretty')
                ->title( __('inventory::inventory.document_status.0') ),

            Column::make('actions'),
        ];
    }

}
