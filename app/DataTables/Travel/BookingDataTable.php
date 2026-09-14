<?php

namespace App\DataTables\Travel;

use App\DataTables\BaseDataTable;
use App\Models\BookingGroup;
use Yajra\DataTables\Html\Column;

class BookingDataTable extends BaseDataTable
{

    private $viewPermission;
    private $editPermission;
    private $deletePermission;

    public function __construct()
    {
        parent::__construct();
        $this->viewPermission = user()->permission('view_booking');
        $this->editPermission = user()->permission('edit_booking');
        $this->deletePermission = user()->permission('delete_booking');
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

                $action .= '<a href="' . route('bookings.show', [$row->id]) . '" class="dropdown-item"><i class="fa fa-eye mr-2"></i>' . __('app.view') . '</a>';

                if (in_array($this->editPermission, ['all', 'added'])) {
                    $action .= '<a class="dropdown-item openRightModal" href="' . route('bookings.edit', [$row->id]) . '">
                                <i class="fa fa-edit mr-2"></i>
                                ' . trans('app.edit') . '
                            </a>';
                }

                if (in_array($this->deletePermission, ['all', 'added'])) {
                    $action .= '<a class="dropdown-item delete-table-row" href="javascript:;" data-booking-id="' . $row->id . '">
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
                    'confirmed' => 'text-dark-green',
                    'cancelled' => 'text-red',
                    'completed' => 'text-blue',
                ];
                $color = $statusColors[$row->status] ?? 'text-dark-green';
                return '<i class="fa fa-circle mr-1 ' . $color . ' f-10"></i>' . __('app.' . $row->status);
            })
            ->editColumn('departure_date', function ($row) {
                return $row->departure_date ? $row->departure_date->format(company()->date_format) : '--';
            })
            ->editColumn('return_date', function ($row) {
                return $row->return_date ? $row->return_date->format(company()->date_format) : '--';
            })
            ->addIndexColumn()
            ->setRowId(fn($row) => 'row-' . $row->id)
            ->rawColumns(['action', 'check', 'status']);
    }

    public function query(BookingGroup $model)
    {
        $model = $model->select('*');

        if ($this->viewPermission == 'added') {
            $model = $model->where('added_by', user()->id);
        }

        if ($this->viewPermission == 'owned') {
            $model = $model->where('customer_id', user()->id);
        }

        return $model;
    }

    public function html()
    {
        return $this->setBuilder('bookings-table', 2)
            ->parameters([
                'initComplete' => 'function () {
                    window.LaravelDataTables["bookings-table"].buttons().container()
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
            __('modules.booking.groupName') => ['data' => 'group_name', 'name' => 'group_name', 'title' => __('modules.booking.groupName')],
            __('modules.booking.groupLeader') => ['data' => 'group_leader', 'name' => 'group_leader', 'title' => __('modules.booking.groupLeader')],
            __('modules.booking.totalPax') => ['data' => 'total_pax', 'name' => 'total_pax', 'title' => __('modules.booking.totalPax')],
            __('app.departureDate') => ['data' => 'departure_date', 'name' => 'departure_date', 'title' => __('app.departureDate')],
            __('app.returnDate') => ['data' => 'return_date', 'name' => 'return_date', 'title' => __('app.returnDate')],
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
