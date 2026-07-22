<?php

namespace App\DataTables\Travel;

use App\DataTables\BaseDataTable;
use App\Models\Flight;
use Yajra\DataTables\Html\Column;

class FlightDataTable extends BaseDataTable
{

    private $viewPermission;
    private $editPermission;
    private $deletePermission;

    public function __construct()
    {
        parent::__construct();
        $this->viewPermission = user()->permission('view_flight');
        $this->editPermission = user()->permission('edit_flight');
        $this->deletePermission = user()->permission('delete_flight');
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
                    $action .= '<a class="dropdown-item openRightModal" href="' . route('flights.edit', [$row->id]) . '">
                                <i class="fa fa-edit mr-2"></i>
                                ' . trans('app.edit') . '
                            </a>';
                }

                if (in_array($this->deletePermission, ['all', 'added'])) {
                    $action .= '<a class="dropdown-item delete-table-row" href="javascript:;" data-flight-id="' . $row->id . '">
                                <i class="fa fa-trash mr-2"></i>
                                ' . trans('app.delete') . '
                            </a>';
                }

                $action .= '</div>
                    </div>
                </div>';

                return $action;
            })
            ->editColumn('departure_time', function ($row) {
                return $row->departure_time ? $row->departure_time->format('H:i') : '--';
            })
            ->editColumn('arrival_time', function ($row) {
                return $row->arrival_time ? $row->arrival_time->format('H:i') : '--';
            })
            ->addIndexColumn()
            ->setRowId(fn($row) => 'row-' . $row->id)
            ->rawColumns(['action', 'check']);
    }

    public function query(Flight $model)
    {
        return $model->select('*');
    }

    public function html()
    {
        return $this->setBuilder('flights-table', 2)
            ->parameters([
                'initComplete' => 'function () {
                    window.LaravelDataTables["flights-table"].buttons().container()
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
            __('modules.flight.flightNumber') => ['data' => 'flight_number', 'name' => 'flight_number', 'title' => __('modules.flight.flightNumber')],
            __('app.origin') => ['data' => 'origin', 'name' => 'origin', 'title' => __('app.origin')],
            __('app.destination') => ['data' => 'destination', 'name' => 'destination', 'title' => __('app.destination')],
            __('modules.flight.departureTime') => ['data' => 'departure_time', 'name' => 'departure_time', 'title' => __('modules.flight.departureTime')],
            __('modules.flight.arrivalTime') => ['data' => 'arrival_time', 'name' => 'arrival_time', 'title' => __('modules.flight.arrivalTime')],
            Column::computed('action', __('app.action'))
                ->exportable(false)
                ->printable(false)
                ->orderable(false)
                ->searchable(false)
                ->addClass('text-right pr-20')
        ];
    }

}
