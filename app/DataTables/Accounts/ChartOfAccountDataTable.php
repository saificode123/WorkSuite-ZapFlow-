<?php

namespace App\DataTables\Accounts;

use App\Models\ChartOfAccount;
use App\DataTables\BaseDataTable;
use Yajra\DataTables\Html\Column;

class ChartOfAccountDataTable extends BaseDataTable
{
    private $viewPermission;

    public function __construct()
    {
        parent::__construct();
        $this->viewPermission = user()->permission('view_chart_of_account');
    }

    public function dataTable($query)
    {
        $datatables = datatables()->eloquent($query);
        $datatables->addIndexColumn();
        $datatables->addColumn('action', function ($row) {
            $action = '<div class="task_view"><div class="dropdown dropup"><a class="task_view_more d-flex align-items-center justify-content-center dropdown-toggle" type="link" id="dropdownMenuLink-' . $row->id . '" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="icon-options-vertical icons"></i></a><div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuLink-' . $row->id . '" tabindex="0">';
            $action .= '<a href="' . route('chart-of-accounts.edit', [$row->id]) . '" class="dropdown-item"><i class="fa fa-edit mr-2"></i>' . __('app.edit') . '</a>';
            $action .= '<a class="dropdown-item delete-table-row" href="javascript:;" data-row-id="' . $row->id . '"><i class="fa fa-trash mr-2"></i>' . trans('app.delete') . '</a>';
            $action .= '</div></div></div>';
            return $action;
        });
        $datatables->editColumn('type', function ($row) {
            return '<span class="badge badge-' . ($row->type == 'asset' || $row->type == 'expense' ? 'primary' : 'success') . '">' . ucfirst($row->type) . '</span>';
        });
        $datatables->editColumn('parent_name', function ($row) {
            return $row->parent ? $row->parent->name : '--';
        });
        $datatables->rawColumns(['action', 'type']);
        return $datatables;
    }

    public function query()
    {
        $model = ChartOfAccount::with('parent')
            ->where('chart_of_accounts.company_id', company()->id);

        if ($this->viewPermission == 'added') {
            $model = $model->where('chart_of_accounts.added_by', user()->id);
        }

        return $model;
    }

    public function html()
    {
        return $this->setBuilder('chart-of-accounts-table')
            ->parameters([
                'initComplete' => 'function () {
                    window.LaravelDataTables["chart-of-accounts-table"].buttons().container()
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
            __('app.code') => ['data' => 'code', 'name' => 'code', 'title' => __('app.code')],
            __('app.name') => ['data' => 'name', 'name' => 'name', 'title' => __('app.name')],
            __('modules.accounts.type') => ['data' => 'type', 'name' => 'type', 'title' => __('modules.accounts.type')],
            __('modules.accounts.parentAccount') => ['data' => 'parent_name', 'name' => 'parent.name', 'title' => __('modules.accounts.parentAccount')],
            __('modules.accounts.level') => ['data' => 'level', 'name' => 'level', 'title' => __('modules.accounts.level')],
            Column::computed('action', __('app.action'))
                ->exportable(false)
                ->printable(false)
                ->orderable(false)
                ->searchable(false)
                ->addClass('text-right pr-20'),
        ];
    }
}
