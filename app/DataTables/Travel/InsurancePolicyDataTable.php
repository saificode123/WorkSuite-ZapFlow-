<?php

namespace App\DataTables\Travel;

use App\DataTables\BaseDataTable;
use App\Models\InsurancePolicy;
use Yajra\DataTables\Html\Column;

class InsurancePolicyDataTable extends BaseDataTable
{
    private $viewPermission;
    private $editPermission;
    private $deletePermission;

    public function __construct()
    {
        parent::__construct();
        $this->viewPermission   = user()->permission('view_insurance_policy');
        $this->editPermission   = user()->permission('edit_insurance_policy');
        $this->deletePermission = user()->permission('delete_insurance_policy');
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

                if (in_array($this->editPermission, ['all', 'added'])) {
                    $action .= '<a class="dropdown-item openRightModal" href="' . route('insurance-policies.edit', [$row->id]) . '">
                                <i class="fa fa-edit mr-2"></i>
                                ' . trans('app.edit') . '
                            </a>';
                }

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
            ->editColumn('provider_name', fn($row) => '<a href="' . route('insurance-policies.edit', [$row->id]) . '" class="openRightModal">' . e($row->provider_name) . '</a>')
            ->editColumn('rate', fn($row) => number_format((float) $row->rate, 2))
            ->editColumn('is_active', fn($row) => $row->is_active
                ? '<span class="badge badge-success">' . trans('app.active') . '</span>'
                : '<span class="badge badge-secondary">' . trans('app.inactive') . '</span>')
            ->addIndexColumn()
            ->setRowId(fn($row) => 'row-' . $row->id)
            ->rawColumns(['action', 'check', 'provider_name', 'is_active']);
    }

    public function query(InsurancePolicy $model)
    {
        $model = $model->select('*');

        if ($this->viewPermission == 'added') {
            $model = $model->where('added_by', user()->id);
        }

        return $model;
    }

    public function html()
    {
        return $this->setBuilder('insurance-policies-table', 1)
            ->parameters([
                'initComplete' => 'function () {
                    window.LaravelDataTables["insurance-policies-table"].buttons().container()
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
            __('modules.insurance.provider') => ['data' => 'provider_name', 'name' => 'provider_name', 'title' => __('modules.insurance.provider')],
            __('modules.insurance.policyType') => ['data' => 'policy_type', 'name' => 'policy_type', 'title' => __('modules.insurance.policyType')],
            __('modules.insurance.policyNumber') => ['data' => 'policy_number', 'name' => 'policy_number', 'title' => __('modules.insurance.policyNumber')],
            __('modules.insurance.rate') => ['data' => 'rate', 'name' => 'rate', 'title' => __('modules.insurance.rate')],
            __('app.status') => ['data' => 'is_active', 'name' => 'is_active', 'title' => __('app.status')],
            Column::computed('action', __('app.action'))
                ->exportable(false)
                ->printable(false)
                ->orderable(false)
                ->searchable(false)
                ->addClass('text-right pr-20')
        ];
    }
}
