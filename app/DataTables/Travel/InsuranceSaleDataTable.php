<?php

namespace App\DataTables\Travel;

use App\DataTables\BaseDataTable;
use App\Models\InsuranceSale;
use Yajra\DataTables\Html\Column;

class InsuranceSaleDataTable extends BaseDataTable
{
    private $viewPermission;
    private $deletePermission;

    public function __construct()
    {
        parent::__construct();
        $this->viewPermission   = user()->permission('view_insurance_sale');
        $this->deletePermission = user()->permission('delete_insurance_sale');
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
            ->addColumn('passenger_name', fn($row) => $row->passenger
                ? e($row->passenger->first_name . ' ' . $row->passenger->family_name)
                : '—')
            ->addColumn('policy_name', fn($row) => $row->policy
                ? e($row->policy->provider_name . ' (' . $row->policy->policy_number . ')')
                : '—')
            ->addColumn('booking_group_no', fn($row) => $row->bookingGroup
                ? e($row->bookingGroup->group_no . ' — ' . $row->bookingGroup->group_name)
                : '—')
            ->editColumn('amount', fn($row) => number_format((float) $row->amount, 2) . ' ' . $row->currency_code)
            ->editColumn('status', fn($row) => '<span class="badge badge-' . ($row->status === 'active' ? 'success' : ($row->status === 'cancelled' ? 'danger' : 'warning')) . '">' . e($row->status) . '</span>')
            ->addIndexColumn()
            ->setRowId(fn($row) => 'row-' . $row->id)
            ->rawColumns(['action', 'check', 'status']);
    }

    public function query(InsuranceSale $model)
    {
        return $model->with(['policy', 'bookingGroup', 'passenger']);
    }

    public function html()
    {
        return $this->setBuilder('insurance-sales-table', 1)
            ->parameters([
                'initComplete' => 'function () {
                    window.LaravelDataTables["insurance-sales-table"].buttons().container()
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
            __('modules.insurance.passenger') => ['data' => 'passenger_name', 'name' => 'passenger.first_name', 'title' => __('modules.insurance.passenger')],
            __('modules.insurance.policy') => ['data' => 'policy_name', 'name' => 'policy.provider_name', 'title' => __('modules.insurance.policy')],
            __('modules.insurance.booking') => ['data' => 'booking_group_no', 'name' => 'bookingGroup.group_no', 'title' => __('modules.insurance.booking')],
            __('modules.insurance.certificateNumber') => ['data' => 'certificate_number', 'name' => 'certificate_number', 'title' => __('modules.insurance.certificateNumber')],
            __('app.amount') => ['data' => 'amount', 'name' => 'amount', 'title' => __('app.amount')],
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
