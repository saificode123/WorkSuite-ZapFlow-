<?php

namespace App\DataTables\Travel;

use App\DataTables\BaseDataTable;
use App\Models\HotelRoom;
use Yajra\DataTables\Html\Column;

class HotelRoomDataTable extends BaseDataTable
{
    private $editPermission;
    private $deletePermission;

    public function __construct()
    {
        parent::__construct();
        $this->editPermission   = user()->permission('edit_hotel');
        $this->deletePermission = user()->permission('delete_hotel');
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
                    $action .= '<a class="dropdown-item openRightModal" href="' . route('hotel-rooms.edit', [$row->id]) . '">
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
            ->addColumn('hotel_name', fn($row) => $row->hotel?->name ?? '—')
            ->editColumn('capacity', fn($row) => (int) $row->capacity)
            ->editColumn('is_available', fn($row) => $row->is_available
                ? '<span class="badge badge-success">' . trans('app.available') . '</span>'
                : '<span class="badge badge-secondary">' . trans('app.unavailable') . '</span>')
            ->addIndexColumn()
            ->setRowId(fn($row) => 'row-' . $row->id)
            ->rawColumns(['action', 'check', 'is_available']);
    }

    public function query(HotelRoom $model)
    {
        return $model->with('hotel');
    }

    public function html()
    {
        return $this->setBuilder('hotel-rooms-table', 1)
            ->parameters([
                'initComplete' => 'function () {
                    window.LaravelDataTables["hotel-rooms-table"].buttons().container()
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
            __('modules.hotel.hotelName') => ['data' => 'hotel_name', 'name' => 'hotel.name', 'title' => __('modules.hotel.hotelName')],
            __('modules.room.roomNumber') => ['data' => 'room_number', 'name' => 'room_number', 'title' => __('modules.room.roomNumber')],
            __('modules.room.roomType') => ['data' => 'room_type', 'name' => 'room_type', 'title' => __('modules.room.roomType')],
            __('modules.room.floor') => ['data' => 'floor', 'name' => 'floor', 'title' => __('modules.room.floor')],
            __('modules.room.capacity') => ['data' => 'capacity', 'name' => 'capacity', 'title' => __('modules.room.capacity')],
            __('modules.room.genderRestriction') => ['data' => 'gender_restriction', 'name' => 'gender_restriction', 'title' => __('modules.room.genderRestriction')],
            __('app.status') => ['data' => 'is_available', 'name' => 'is_available', 'title' => __('app.status')],
            Column::computed('action', __('app.action'))
                ->exportable(false)
                ->printable(false)
                ->orderable(false)
                ->searchable(false)
                ->addClass('text-right pr-20')
        ];
    }
}
