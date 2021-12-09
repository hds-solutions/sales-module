<?php

namespace HDSSolutions\Laravel\Reports;

use HDSSolutions\Laravel\DataTables\Base\DataTable;
use HDSSolutions\Laravel\Models\Invoice as Resource;
use HDSSolutions\Laravel\Traits\DatatableWithPartnerable;
use HDSSolutions\Laravel\Traits\DatatableWithCurrency;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Html\Column;

class SaleInvoicesReport extends DataTable {
    use DatatableWithPartnerable;
    use DatatableWithCurrency;

    protected array $with = [
        'partnerable',
        'currency',
    ];

    protected array $orderBy = [
        'transacted_at' => 'desc',
    ];

    public function __construct() {
        parent::__construct(
            Resource::class,
            route('backend.reports.sales.invoices'),
        );
    }

    protected function getColumns() {
        return [
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

            Column::make('total')
                ->title( __('sales::invoice.total.0') )
                ->renderRaw('view:invoice')
                ->data( view('sales::invoices.datatable.total')->render() )
                ->class('text-right'),
        ];
    }

    protected function joins(Builder $query):Builder {
        // add custom JOIN to customers + people for Partnerable
        return $query
            // join to partnerable
            ->leftJoin('customers', 'customers.id', 'invoices.partnerable_id')
            // join to people
            ->join('people', 'people.id', 'customers.id');
    }

    protected function filters(Builder $query):Builder {
        // load Sale Invoices only
        return $query->isSale()->completed();
    }

    protected function append(Builder $query, $data) {
        // add invoices total amount
        $data['total'] = amount($query->sum('invoices.total'), request('filters')['currency']);
        // return data
        return $data;
    }

    protected function getTableId():string {
        return class_basename($this->resource).'-report';
    }

    protected function parameters():array {
        return [
            'info'      => false,
            'paging'    => false,
            'searching' => false,
        ];
    }

    protected function filterBranch(Builder $query, int $branch_id):Builder {
        // filter only from Branch
        return $query->where('invoices.branch_id', $branch_id);
    }

    protected function filterTransactedAt(Builder $query, $daterange):Builder {
        // filter transacted at
        return $query->whereBetween('transacted_at', explode(' - ', $daterange, 2));
    }

}
