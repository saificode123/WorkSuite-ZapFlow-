<?php

namespace App\DataTables\Travel;

use App\DataTables\BaseDataTable;
use App\Models\TicketInvoice;
use Yajra\DataTables\Html\Column;

class TicketInvoiceDataTable extends BaseDataTable
{

    private $viewPermission;
    private $editPermission;
    private $deletePermission;

    public function __construct()
    {
        parent::__construct();
        $this->viewPermission = user()->permission('view_ticket_invoice');
        $this->editPermission = user()->permission('edit_ticket_invoice');
        $this->deletePermission = user()->permission('delete_ticket_invoice');
    }

    public function dataTable($query)
    {
        return datatables()
            ->eloquent($query)
            ->addColumn('check', fn($row) => $this->checkBox($row))
            ->addColumn('action', function ($row) {
                $action = '<div class="task_view">
                    <div class="dropdown">
                        <a class="task_view_more d-flex align-items-center justify-content-center dropdown-toggle" type="link"
                            id="dropdownMenuLink-' . $row->id . '" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="icon-options-vertical icons"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuLink-' . $row->id . '" tabindex="0">';

                $action .= '<a href="' . route('ticketing.show', [$row->id]) . '" class="dropdown-item"><i class="fa fa-eye mr-2"></i>' . __('app.view') . '</a>';

                if (in_array($this->editPermission, ['all', 'added'])) {
                    $action .= '<a class="dropdown-item openRightModal" href="' . route('ticketing.edit', [$row->id]) . '">
                                <i class="fa fa-edit mr-2"></i>
                                ' . trans('app.edit') . '
                            </a>';
                }

                if (in_array($this->deletePermission, ['all', 'added'])) {
                    $action .= '<a class="dropdown-item delete-table-row" href="javascript:;" data-ticket-invoice-id="' . $row->id . '">
                                <i class="fa fa-trash mr-2"></i>
                                ' . trans('app.delete') . '
                            </a>';
                }

                $action .= '</div>
                    </div>
                </div>';

                return $action;
            })
            ->editColumn('status', function ($row) {
                $statusColors = [
                    'pending' => 'text-yellow',
                    'paid' => 'text-dark-green',
                    'cancelled' => 'text-red',
                ];
                $color = $statusColors[$row->status] ?? 'text-dark-green';
                return '<i class="fa fa-circle mr-1 ' . $color . ' f-10"></i>' . __('app.' . $row->status);
            })
            ->editColumn('sale_type', function ($row) {
                $type = strtoupper($row->sale_type ?? 'direct');
                $badgeClass = match(strtolower($row->sale_type ?? 'direct')) {
                    'bsp' => 'badge-info',
                    'xo' => 'badge-primary',
                    default => 'badge-secondary',
                };
                return '<span class="badge ' . $badgeClass . '">' . $type . '</span>';
            })
            ->editColumn('total_amount', function ($row) {
                return currency_format($row->total_amount, company()->currency_id);
            })
            ->addIndexColumn()
            ->setRowId(fn($row) => 'row-' . $row->id)
            ->rawColumns(['action', 'check', 'status', 'sale_type']);

    }

    public function query(TicketInvoice $model)
    {
        $model = $model->select('*');

        if ($this->viewPermission == 'added') {
            $model = $model->where('added_by', user()->id);
        }

        return $model;
    }

    public function html()
    {
        return $this->setBuilder('ticket-invoices-table', 2)
            ->parameters([
                'initComplete' => 'function () {
                    window.LaravelDataTables["ticket-invoices-table"].buttons().container()
                    .appendTo("#table-actions")
                }',
                'fnDrawCallback' => 'function( oSettings ) {
                    $("body").tooltip({
                        selector: \'[data-toggle="tooltip"]\'
                    })
                }',
            ]);
    }

    protected function getColumns()
    {
        return [
            'check' => [
                'title' => '<input type="checkbox" name="select_all_table" id="select-all-table" onclick="selectAllTable(this)">',
                'exportable' => false,
                'orderable' => false,
                'searchable' => false
            ],
            '#' => ['data' => 'DT_RowIndex', 'orderable' => false, 'searchable' => false, 'visible' => false, 'title' => '#'],
            __('modules.ticketing.invoiceNumber') => ['data' => 'invoice_number', 'name' => 'invoice_number', 'title' => __('modules.ticketing.invoiceNumber')],
            __('app.date') => ['data' => 'date', 'name' => 'date', 'title' => __('app.date')],
            __('modules.ticketing.totalAmount') => ['data' => 'total_amount', 'name' => 'total_amount', 'title' => __('modules.ticketing.totalAmount')],
            __('modules.ticketing.ticketCount') => ['data' => 'ticket_count', 'name' => 'ticket_count', 'title' => __('modules.ticketing.ticketCount')],
            'Sale Type' => ['data' => 'sale_type', 'name' => 'sale_type', 'title' => 'Sale Type'],
            __('app.status') => ['data' => 'status', 'name' => 'status', 'title' => __('app.status')],
            Column::computed('action', __('app.action'))
                ->exportable(false)
                ->printable(false)
                ->orderable(false)
                ->searchable(false)
                ->addClass('text-right pr-20')
        ];
    }

}
