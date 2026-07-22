<?php

namespace App\DataTables\Accounts;

use App\Models\JournalVoucher;
use App\DataTables\BaseDataTable;
use Yajra\DataTables\Html\Column;
use Illuminate\Support\Facades\DB;

class JournalVoucherDataTable extends BaseDataTable
{
    private $viewPermission;

    public function __construct()
    {
        parent::__construct();
        $this->viewPermission = user()->permission('view_journal_voucher');
    }

    public function dataTable($query)
    {
        $datatables = datatables()->eloquent($query);
        $datatables->addIndexColumn();
        $datatables->addColumn('action', function ($row) {
            $action = '<div class="task_view"><div class="dropdown dropup"><a class="task_view_more d-flex align-items-center justify-content-center dropdown-toggle" type="link" id="dropdownMenuLink-' . $row->id . '" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="icon-options-vertical icons"></i></a><div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuLink-' . $row->id . '" tabindex="0">';
            $action .= '<a href="' . route('journal-vouchers.show', [$row->id]) . '" class="dropdown-item"><i class="fa fa-eye mr-2"></i>' . __('app.view') . '</a>';
            $action .= '<a href="' . route('journal-vouchers.edit', [$row->id]) . '" class="dropdown-item"><i class="fa fa-edit mr-2"></i>' . __('app.edit') . '</a>';
            $action .= '<a href="' . route('journal-vouchers.print', [$row->id]) . '" class="dropdown-item" target="_blank"><i class="fa fa-print mr-2"></i>' . __('app.print') . '</a>';
            $action .= '<a class="dropdown-item delete-table-row" href="javascript:;" data-row-id="' . $row->id . '"><i class="fa fa-trash mr-2"></i>' . trans('app.delete') . '</a>';
            $action .= '</div></div></div>';
            return $action;
        });
        $datatables->editColumn('is_balanced', function ($row) {
            return $row->is_balanced
                ? '<span class="badge badge-success">' . __('app.yes') . '</span>'
                : '<span class="badge badge-danger">' . __('app.no') . '</span>';
        });
        $datatables->editColumn('financial_year', function ($row) {
            return $row->financialYear ? $row->financialYear->name : '--';
        });
        $datatables->editColumn('created_by', function ($row) {
            return $row->createdBy ? $row->createdBy->name : '--';
        });
        $datatables->editColumn('date', function ($row) {
            return $row->date->timezone($this->company->timezone)->translatedFormat($this->company->date_format);
        });
        $datatables->rawColumns(['action', 'is_balanced']);
        return $datatables;
    }

    public function query()
    {
        $model = JournalVoucher::with('financialYear', 'createdBy')
            ->where('company_id', company()->id);

        if ($this->viewPermission == 'added') {
            $model = $model->where('created_by', user()->id);
        }

        return $model;
    }

    public function html()
    {
        return $this->setBuilder('journal-vouchers-table')
            ->parameters([
                'initComplete' => 'function () {
                    window.LaravelDataTables["journal-vouchers-table"].buttons().container()
                    .appendTo("#table-actions");
                }',
                'fnDrawCallback' => 'function(oSettings) {
                    $("body").tooltip({selector: \'[data-toggle="tooltip"]\'});
                }',
            ]);
    }

    protected function getColumns()
    {
        return [
            __('app.id') => ['data' => 'id', 'name' => 'id', 'visible' => false, 'title' => __('app.id')],
            __('modules.accounts.voucherNumber') => ['data' => 'voucher_number', 'name' => 'voucher_number', 'title' => __('modules.accounts.voucherNumber')],
            __('app.date') => ['data' => 'date', 'name' => 'date', 'title' => __('app.date')],
            __('modules.accounts.financialYear') => ['data' => 'financial_year', 'name' => 'financialYear.name', 'title' => __('modules.accounts.financialYear')],
            __('modules.accounts.narration') => ['data' => 'narration', 'name' => 'narration', 'title' => __('modules.accounts.narration')],
            __('modules.accounts.isBalanced') => ['data' => 'is_balanced', 'name' => 'is_balanced', 'title' => __('modules.accounts.isBalanced')],
            __('app.createdBy') => ['data' => 'created_by', 'name' => 'createdBy.name', 'title' => __('app.createdBy')],
            Column::computed('action', __('app.action'))
                ->exportable(false)
                ->printable(false)
                ->orderable(false)
                ->searchable(false)
                ->addClass('text-right pr-20'),
        ];
    }
}
