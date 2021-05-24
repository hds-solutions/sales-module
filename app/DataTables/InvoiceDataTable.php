<?php

namespace HDSSolutions\Finpar\DataTables;

use HDSSolutions\Finpar\Models\Invoice as Resource;
use Yajra\DataTables\Html\Column;

class InvoiceDataTable extends Base\DataTable {

    protected array $with = [
        'partnerable',
        'currency',
    ];

    public function __construct() {
        parent::__construct(
            Resource::class,
            route('backend.invoices'),
        );
    }

    protected function getColumns() {
        return [
            Column::computed('id')
                ->title( __('sales::invoice.id.0') )
                ->hidden(),

            Column::make('document_number')
                ->title( __('sales::invoice.document_number.0') ),

            Column::computed('transacted_at')
                ->title( __('sales::invoice.transacted_at.0') )
                ->renderRaw('datetime:transacted_at;F j, Y H:i'),

            Column::make('partnerable.full_name')
                ->title( __('sales::invoice.partnerable_id.0') ),

            // Column::make('currency.name')
            //     ->title( __('sales::invoice.currency_id.0') ),

            Column::make('is_credit')
                ->title( __('sales::invoice.payment_rule.0') )
                ->renderRaw('view:invoice')
                ->data( view('sales::invoices.datatable.is_credit')->render() ),

            Column::make('document_status_pretty')
                ->title( __('inventory::inventory.document_status.0') ),

            Column::make('total')
                ->title( __('sales::invoice.total.0') )
                ->renderRaw('view:invoice')
                ->data( view('sales::invoices.datatable.total')->render() ),

            Column::make('paid_amount')
                ->title( __('sales::invoice.paid_amount.0') )
                ->renderRaw('view:invoice')
                ->data( view('sales::invoices.datatable.paid_amount')->render() ),

            Column::make('actions'),
        ];
    }

}
