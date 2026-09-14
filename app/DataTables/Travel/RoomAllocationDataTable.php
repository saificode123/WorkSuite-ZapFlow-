<?php

namespace App\DataTables\Travel;

use App\DataTables\BaseDataTable;
use App\Models\RoomAllocation;
use Yajra\DataTables\Html\Column;

class RoomAllocationDataTable extends BaseDataTable
{
    public function dataTable($query)
    {
        return datatables()
            ->eloquent($query)
            ->addColumn('check', fn($row) => $this->checkBox($row))
            ->addColumn('room_number', fn($row) => $row->hotelRoom?->room_number ?? '—')
            ->addColumn('hotel_name', fn($row) => $row->hotelRoom?->hotel?->name ?? '—')
            ->addColumn('passenger_name', fn($row) => $row->passenger
                ? e($row->passenger->first_name . ' ' . $row->passenger->family_name)
                : '—')
            ->addColumn('booking_group', fn($row) => $row->bookingGroup
                ? e($row->bookingGroup->group_no . ' — ' . $row->bookingGroup->group_name)
                : '—')
            ->editColumn('status', fn($row) => '<span class="badge badge-' . match ($row->status) {
                'reserved'   => 'info',
                'checked_in' => 'success',
                'checked_out'=> 'secondary',
                'cancelled'  => 'danger',
                default      => 'light',
            } . '">' . e(str_replace('_', ' ', $row->status)) . '</span>')
            ->addColumn('action', function ($row) {
                return '<button class="btn btn-sm btn-danger remove-allocation-btn" data-allocation-id="' . $row->id . '" data-passenger-id="' . $row->passenger_id . '">
                            <i class="fa fa-trash"></i>
                        </button>';
            })
            ->addIndexColumn()
            ->setRowId(fn($row) => 'row-' . $row->id)
            ->rawColumns(['action', 'check', 'status']);
    }

    public function query(RoomAllocation $model)
    {
        return $model->with(['hotelRoom.hotel', 'passenger', 'bookingGroup']);
    }

    public function html()
    {
        return $this->setBuilder('room-allocations-table', 1)
            ->parameters([
                'initComplete' => 'function () {
                    window.LaravelDataTables["room-allocations-table"].buttons().container()
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
            __('modules.room.hotel') => ['data' => 'hotel_name', 'name' => 'hotelRoom.hotel.name', 'title' => __('modules.room.hotel')],
            __('modules.room.roomNumber') => ['data' => 'room_number', 'name' => 'hotelRoom.room_number', 'title' => __('modules.room.roomNumber')],
            __('modules.room.passenger') => ['data' => 'passenger_name', 'name' => 'passenger.first_name', 'title' => __('modules.room.passenger')],
            __('modules.room.booking') => ['data' => 'booking_group', 'name' => 'bookingGroup.group_no', 'title' => __('modules.room.booking')],
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
