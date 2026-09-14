<?php

namespace App\DataTables\Travel;

use App\DataTables\BaseDataTable;
use App\Models\CashReceipt;
use Yajra\DataTables\Html\Column;

class CashReceiptDataTable extends BaseDataTable
{
    private $deletePermission;

    public function __construct()
    {
        parent::__construct();
        $this->deletePermission = user()->permission('delete_cash_receipt');
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

                if (in_array($this->deletePermission, ['all', 'added'])) {
                    $action .= '<a class="dropdown-item delete-table-row" href="javascript:;" data-row-id="' . $row->id . '">
                                <i class="fa fa-trash mr-2"></i>
                                ' . trans('app.delete') . '
                            </a>';
                }

                $action .= '</div>
                    </div>
                </div>';

                return $action;
            })
            ->addColumn('account_name', fn($row) => $row->account?->name ?? '—')
            ->addColumn('added_by_name', fn($row) => $row->addedBy?->name ?? '—')
            ->addColumn('jv_number', fn($row) => $row->journalVoucher?->voucher_number ?? '—')
            ->editColumn('amount', fn($row) => number_format((float) $row->amount, 2) . ' ' . $row->currency_code)
            ->editColumn('date', fn($row) => $row->date ? \Carbon\Carbon::parse($row->date)->format(companyOrGlobalSetting()->date_format ?? 'Y-m-d') : '—')
            ->addIndexColumn()
            ->setRowId(fn($row) => 'row-' . $row->id)
            ->rawColumns(['action', 'check']);
    }

    public function query(CashReceipt $model)
    {
        return $model->with(['account', 'addedBy', 'journalVoucher']);
    }

    public function html()
    {
        return $this->setBuilder('cash-receipts-table', 1)
            ->parameters([
                'initComplete' => 'function () {
                    window.LaravelDataTables["cash-receipts-table"].buttons().container()
                    .appendTo("#table-actions")
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
            __('app.date') => ['data' => 'date', 'name' => 'date', 'title' => __('app.date')],
            __('modules.cashReceipts.receivedFrom') => ['data' => 'received_from', 'name' => 'received_from', 'title' => __('modules.cashReceipts.receivedFrom')],
            __('modules.cashReceipts.account') => ['data' => 'account_name', 'name' => 'account.name', 'title' => __('modules.cashReceipts.account')],
            __('app.amount') => ['data' => 'amount', 'name' => 'amount', 'title' => __('app.amount')],
            __('modules.cashReceipts.referenceNo') => ['data' => 'reference_no', 'name' => 'reference_no', 'title' => __('modules.cashReceipts.referenceNo')],
            __('modules.cashReceipts.jvNumber') => ['data' => 'jv_number', 'name' => 'journalVoucher.voucher_number', 'title' => __('modules.cashReceipts.jvNumber')],
            __('app.addedBy') => ['data' => 'added_by_name', 'name' => 'addedBy.name', 'title' => __('app.addedBy')],
            Column::computed('action', __('app.action'))
                ->exportable(false)
                ->printable(false)
                ->orderable(false)
                ->searchable(false)
                ->addClass('text-right pr-20')
        ];
    }
}
