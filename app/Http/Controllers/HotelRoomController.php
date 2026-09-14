<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use App\Models\HotelRoom;
use App\Models\Hotel;
use App\DataTables\Travel\HotelRoomDataTable;
use Illuminate\Http\Request;

class HotelRoomController extends AccountBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.hotelRooms';
    }

    public function index(HotelRoomDataTable $dataTable, Request $request)
    {
        $viewPermission = user()->permission('view_hotel');
        abort_403(!in_array($viewPermission, ['all', 'added', 'owned', 'both']));
        $this->hotels = Hotel::orderBy('name')->get();
        return $dataTable->render('travel.hotel-rooms.index', $this->data);
    }

    public function create()
    {
        $this->hotels = Hotel::orderBy('name')->get();
        $this->view = 'travel.hotel-rooms.ajax.create';

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }
        return view('travel.hotel-rooms.create', $this->data);
    }

    public function store(Request $request)
    {
        $request->validate([
            'hotel_id'    => 'required|exists:hotels,id',
            'room_number' => 'nullable|string|max:50',
            'room_type'   => 'required|in:single,double,triple,quad,quint',
            'capacity'    => 'required|integer|min:1|max:10',
            'floor'       => 'nullable|string|max:10',
        ]);

        HotelRoom::create($request->only([
            'hotel_id', 'room_number', 'room_type', 'capacity',
            'floor', 'gender_restriction', 'notes',
        ]));

        return Reply::successWithData(__('messages.recordSaved'), ['redirectUrl' => route('hotel-rooms.index')]);
    }

    public function edit($id)
    {
        $this->room = HotelRoom::findOrFail($id);
        $this->hotels = Hotel::orderBy('name')->get();
        $this->view = 'travel.hotel-rooms.ajax.edit';

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }
        return view('travel.hotel-rooms.create', $this->data);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'hotel_id'    => 'required|exists:hotels,id',
            'room_type'   => 'required|in:single,double,triple,quad,quint',
            'capacity'    => 'required|integer|min:1|max:10',
        ]);

        HotelRoom::findOrFail($id)->update($request->only([
            'hotel_id', 'room_number', 'room_type', 'capacity',
            'floor', 'gender_restriction', 'notes',
        ]));

        return Reply::successWithData(__('messages.updateSuccess'), ['redirectUrl' => route('hotel-rooms.index')]);
    }

    public function destroy($id)
    {
        HotelRoom::findOrFail($id)->delete();
        return Reply::successWithData(__('messages.deleteSuccess'), ['redirectUrl' => route('hotel-rooms.index')]);
    }
}
