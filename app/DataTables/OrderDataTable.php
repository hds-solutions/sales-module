<?php

namespace HDSSolutions\Finpar\DataTables;

use HDSSolutions\Finpar\Models\Order as Resource;
use HDSSolutions\Finpar\Traits\SearchablePartnerable;
use Illuminate\Database\Eloquent\Builder;
use Yajra\DataTables\Html\Column;

class OrderDataTable extends Base\DataTable {
    use SearchablePartnerable;

    protected array $with = [
        'partnerable',
        'currency',
    ];

    protected array $orderBy = [
        'document_status'   => 'asc',
        'transacted_at'     => 'desc',
    ];

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

            Column::make('document_number')
                ->title( __('sales::order.document_number.0') )
                ->renderRaw('bold:document_number'),

            Column::make('transacted_at')
                ->title( __('sales::order.transacted_at.0') )
                ->renderRaw('datetime:transacted_at;F j, Y H:i'),

            Column::make('partnerable.full_name')
                ->title( __('sales::order.partnerable_id.0') ),

            Column::make('currency.name')
                ->title( __('sales::order.currency_id.0') ),

            Column::make('document_status_pretty')
                ->title( __('inventory::inventory.document_status.0') ),

            Column::make('total')
                ->title( __('sales::order.total.0') )
                ->renderRaw('view:order')
                ->data( view('sales::orders.datatable.total')->render() ),

            Column::make('actions'),
        ];
    }

    protected function joins(Builder $query):Builder {
        // add custom JOIN to customers + people for Partnerable
        return $query
            // join to partnerable
            ->leftJoin('customers', 'customers.id', 'orders.partnerable_id')
            // join to people
            ->join('people', 'people.id', 'customers.id');
    }

}
