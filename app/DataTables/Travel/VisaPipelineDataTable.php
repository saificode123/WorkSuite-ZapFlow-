<?php

namespace App\DataTables\Travel;

use App\DataTables\BaseDataTable;
use App\Models\Passenger;
use Yajra\DataTables\Html\Column;

class VisaPipelineDataTable extends BaseDataTable
{
    public function dataTable($query)
    {
        return datatables()
            ->eloquent($query)
            ->addColumn('check', fn($row) => $this->checkBox($row))
            ->addColumn('passenger_name', fn($row) => e($row->first_name . ' ' . $row->family_name))
            ->addColumn('passport_no', fn($row) => e($row->masked_passport_no))
            ->addColumn('booking_group', fn($row) => $row->bookingGroup
                ? e($row->bookingGroup->group_no)
                : '—')
            ->addColumn('current_status', fn($row) => $row->visa_pipeline_status ?? $row->mofa_status)
            ->addColumn('mofa_ref', fn($row) => $row->visa_mofa_ref ?? '—')
            ->addColumn('action', function ($row) {
                $stages = ['draft', 'sent_to_embassy', 'mofa_received', 'issued'];
                $html = '<div class="dropdown">
                            <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" data-toggle="dropdown">' . trans('app.moveTo') . '</button>
                            <div class="dropdown-menu">';
                foreach ($stages as $s) {
                    $html .= '<a class="dropdown-item visa-move-btn" href="javascript:;" data-passenger-id="' . $row->id . '" data-target-status="' . $s . '">' . e($s) . '</a>';
                }
                $html .= '</div></div>';
                return $html;
            })
            ->addIndexColumn()
            ->setRowId(fn($row) => 'row-' . $row->id)
            ->rawColumns(['action', 'check']);
    }

    public function query(Passenger $model)
    {
        return $model->with('bookingGroup');
    }

    public function html()
    {
        return $this->setBuilder('visa-pipeline-table', 1)
            ->parameters([
                'initComplete' => 'function () {
                    window.LaravelDataTables["visa-pipeline-table"].buttons().container()
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
            __('modules.visa.passenger') => ['data' => 'passenger_name', 'name' => 'first_name', 'title' => __('modules.visa.passenger')],
            __('modules.visa.passportNo') => ['data' => 'passport_no', 'name' => 'passport_no', 'title' => __('modules.visa.passportNo')],
            __('modules.visa.booking') => ['data' => 'booking_group', 'name' => 'bookingGroup.group_no', 'title' => __('modules.visa.booking')],
            __('modules.visa.currentStatus') => ['data' => 'current_status', 'name' => 'visa_pipeline_status', 'title' => __('modules.visa.currentStatus')],
            __('modules.visa.mofaRef') => ['data' => 'mofa_ref', 'name' => 'visa_mofa_ref', 'title' => __('modules.visa.mofaRef')],
            Column::computed('action', __('app.action'))
                ->exportable(false)
                ->printable(false)
                ->orderable(false)
                ->searchable(false)
                ->addClass('text-right pr-20')
        ];
    }
}
