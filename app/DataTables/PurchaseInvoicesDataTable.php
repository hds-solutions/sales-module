<?php

namespace HDSSolutions\Laravel\DataTables;

use HDSSolutions\Laravel\Models\Invoice as Resource;
use HDSSolutions\Laravel\Traits\DatatableWithPartnerable;
use HDSSolutions\Laravel\Traits\DatatableWithCurrency;
use HDSSolutions\Laravel\Traits\DatatableAsDocument;
use Illuminate\Database\Eloquent\Builder;
use Yajra\DataTables\Html\Column;

class PurchaseInvoicesDataTable extends Base\DataTable {
    use DatatableWithPartnerable;
    use DatatableWithCurrency;
    use DatatableAsDocument;

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
            route('backend.purchases.invoices'),
        );
    }

    protected function getColumns() {
        return [
            Column::computed('id')
                ->title( __('sales::invoice.id.0') )
                ->hidden(),

            Column::make('document_number')
                ->title( __('sales::invoice.document_number.0') )
                ->renderRaw('bold:document_number'),

            Column::make('transacted_at')
                ->title( __('sales::invoice.transacted_at.0') )
                ->renderRaw('datetime:transacted_at;F j, Y H:i'),

            Column::make('partnerable.full_name')
                ->title( __('sales::invoice.partnerable_id.0') ),

            Column::computed('is_credit')
                ->title( __('sales::invoice.payment_rule.0') )
                ->renderRaw('view:invoice')
                ->data( view('sales::invoices.datatable.is_credit')->render() ),

            Column::make('document_status_pretty')
                ->title( __('sales::invoice.document_status.0') ),

            Column::make('total')
                ->title( __('sales::invoice.total.0') )
                ->renderRaw('view:invoice')
                ->data( view('sales::invoices.datatable.total')->render() )
                ->class('text-right'),

            Column::make('paid_amount')
                ->title( __('sales::invoice.paid_amount.0') )
                ->renderRaw('view:invoice')
                ->data( view('sales::invoices.datatable.paid_amount')->render() )
                ->class('text-right'),

            Column::computed('actions'),
        ];
    }

    protected function joins(Builder $query):Builder {
        // load Purchaes Invoices only
        // add custom JOIN to providers + people for Partnerable
        return $query->isPurchase()
            // join to partnerable
            ->leftJoin('providers', 'providers.id', 'invoices.partnerable_id')
            // join to people
            ->join('people', 'people.id', 'providers.id');
    }

}
