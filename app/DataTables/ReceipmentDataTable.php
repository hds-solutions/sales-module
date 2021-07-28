<?php

namespace HDSSolutions\Laravel\DataTables;

use HDSSolutions\Laravel\Models\Receipment as Resource;
use HDSSolutions\Laravel\Traits\DatatableWithPartnerable;
use HDSSolutions\Laravel\Traits\DatatableWithCurrency;
use Illuminate\Database\Eloquent\Builder;
use Yajra\DataTables\Html\Column;

class ReceipmentDataTable extends Base\DataTable {
    use DatatableWithPartnerable;
    use DatatableWithCurrency;

    protected array $with = [
        'partnerable',
        'currency',
        'invoices',
        'cashLines',
        'credits',
        'checks',
        'creditNotes',
        'promissoryNotes',
        'cards',
    ];

    protected array $orderBy = [
        'document_status'   => 'asc',
        'transacted_at'     => 'desc',
    ];

    public function __construct() {
        parent::__construct(
            Resource::class,
            route('backend.receipments'),
        );
    }

    protected function getColumns() {
        return [
            Column::computed('id')
                ->title( __('sales::receipment.id.0') )
                ->hidden(),

            Column::make('document_number')
                ->title( __('sales::receipment.document_number.0') )
                ->renderRaw('bold:document_number'),

            Column::make('transacted_at')
                ->title( __('sales::receipment.transacted_at.0') )
                ->renderRaw('datetime:transacted_at;F j, Y H:i'),

            Column::make('partnerable.full_name')
                ->title( __('sales::receipment.partnerable_id.0') ),

            Column::computed('invoices_amount')
                ->title( __('sales::receipment.invoices_amount.0') )
                ->renderRaw('view:receipment')
                ->data( view('sales::receipments.datatable.invoices_amount')->render() ),

            Column::computed('payments_amount')
                ->title( __('sales::receipment.payments_amount.0') )
                ->renderRaw('view:receipment')
                ->data( view('sales::receipments.datatable.payments_amount')->render() ),

            Column::make('document_status_pretty')
                ->title( __('inventory::inventory.document_status.0') ),

            Column::computed('actions'),
        ];
    }

    protected function joins(Builder $query):Builder {
        // add custom JOIN to customers + people for Partnerable
        return $query
            // join to partnerable
            ->leftJoin('customers', 'customers.id', 'receipments.partnerable_id')
            // join to people
            ->join('people', 'people.id', 'customers.id');
    }

}
