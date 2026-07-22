<?php

namespace App\DataTables\Accounts;

use App\Models\AccountOpening;
use App\DataTables\BaseDataTable;
use Yajra\DataTables\Html\Column;

class AccountOpeningDataTable extends BaseDataTable
{
    public function dataTable($query)
    {
        $datatables = datatables()->eloquent($query);
        $datatables->addIndexColumn();
        $datatables->addColumn('action', function ($row) {
            $action = '<div class="task_view"><div class="dropdown dropup"><a class="task_view_more d-flex align-items-center justify-content-center dropdown-toggle" type="link" id="dropdownMenuLink-' . $row->id . '" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="icon-options-vertical icons"></i></a><div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuLink-' . $row->id . '" tabindex="0">';
            $action .= '<a href="' . route('account-openings.edit', [$row->id]) . '" class="dropdown-item"><i class="fa fa-edit mr-2"></i>' . __('app.edit') . '</a>';
            $action .= '<a class="dropdown-item delete-table-row" href="javascript:;" data-row-id="' . $row->id . '"><i class="fa fa-trash mr-2"></i>' . trans('app.delete') . '</a>';
            $action .= '</div></div></div>';
            return $action;
        });
        $datatables->editColumn('account_name', function ($row) {
            return $row->account ? $row->account->name : '--';
        });
        $datatables->editColumn('financial_year_name', function ($row) {
            return $row->financialYear ? $row->financialYear->name : '--';
        });
        $datatables->editColumn('opening_balance', function ($row) {
            return currency_format($row->opening_balance, company()->currency->id);
        });
        $datatables->rawColumns(['action']);
        return $datatables;
    }

    public function query()
    {
        return AccountOpening::with('account', 'financialYear')
            ->where('company_id', company()->id);
    }

    public function html()
    {
        return $this->setBuilder('account-openings-table')
            ->parameters([
                'initComplete' => 'function () {
                    window.LaravelDataTables["account-openings-table"].buttons().container()
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
            __('modules.accounts.account') => ['data' => 'account_name', 'name' => 'account.name', 'title' => __('modules.accounts.account')],
            __('modules.accounts.financialYear') => ['data' => 'financial_year_name', 'name' => 'financialYear.name', 'title' => __('modules.accounts.financialYear')],
            __('modules.accounts.openingBalance') => ['data' => 'opening_balance', 'name' => 'opening_balance', 'title' => __('modules.accounts.openingBalance')],
            Column::computed('action', __('app.action'))
                ->exportable(false)
                ->printable(false)
                ->orderable(false)
                ->searchable(false)
                ->addClass('text-right pr-20'),
        ];
    }
}
