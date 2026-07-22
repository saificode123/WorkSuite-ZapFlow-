<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use App\Models\BookingGroup;
use App\Models\HotelRoom;
use App\Models\Passenger;
use App\Models\RoomAllocation;
use Illuminate\Http\Request;

class RoomAllocationController extends AccountBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.roomAllocation';

        $this->middleware(function ($request, $next) {
            abort_403(!in_array('booking', $this->user->modules));
            return $next($request);
        });
    }

    public function index(Request $request)
    {
        $viewPermission = user()->permission('view_booking');
        abort_403(!in_array($viewPermission, ['all', 'added', 'owned', 'both']));

        $this->bookingGroups  = BookingGroup::orderBy('group_name')->get();
        $this->bookingGroup   = $request->booking_group_id
            ? BookingGroup::with('package')->find($request->booking_group_id)
            : null;

        $this->unassignedPassengers = collect();
        $this->rooms                = collect();
        $this->hotel                = null;
        $this->totalPassengers      = 0;
        $this->assignedCount        = 0;

        if ($this->bookingGroup) {
            // Get hotel from the package linked to this booking
            $hotelId = $this->bookingGroup->package?->packageHotels?->first()?->hotel_id;

            if ($hotelId) {
                $this->hotel = \App\Models\Hotel::find($hotelId);
                $this->rooms = HotelRoom::where('hotel_id', $hotelId)
                    ->with([
                        'activeAllocations.passenger',
                    ])
                    ->orderBy('room_number')
                    ->get();
            }

            $allPassengers = Passenger::where('booking_group_id', $this->bookingGroup->id)->get();
            $this->totalPassengers = $allPassengers->count();

            // Find passengers NOT in any active allocation for this booking
            $assignedPassengerIds = RoomAllocation::where('booking_group_id', $this->bookingGroup->id)
                ->whereIn('status', ['reserved', 'checked_in'])
                ->pluck('passenger_id');

            $this->assignedCount        = $assignedPassengerIds->count();
            $this->unassignedPassengers = $allPassengers->whereNotIn('id', $assignedPassengerIds)->values();
        }

        return view('travel.room-allocation.index', $this->data);
    }

    /**
     * Assign a passenger to a room (drag-drop or select).
     */
    public function assign(Request $request)
    {
        $editPermission = user()->permission('edit_booking');
        abort_403(!in_array($editPermission, ['all', 'added']));

        $request->validate([
            'passenger_id'      => 'required|exists:passengers,id',
            'room_id'           => 'required|exists:hotel_rooms,id',
            'booking_group_id'  => 'nullable|exists:booking_groups,id',
        ]);

        $room      = HotelRoom::findOrFail($request->room_id);
        $occupied  = $room->activeAllocations()->count();

        if ($occupied >= $room->capacity) {
            return Reply::error(__('modules.room.roomFull'));
        }

        // Remove existing allocation for this passenger if moving between rooms
        if ($request->filled('old_allocation_id')) {
            RoomAllocation::where('id', $request->old_allocation_id)
                ->where('passenger_id', $request->passenger_id)
                ->update(['status' => 'cancelled']);
        } else {
            // Cancel any previous active allocation for this passenger in this booking
            RoomAllocation::where('passenger_id', $request->passenger_id)
                ->when($request->booking_group_id, fn($q) => $q->where('booking_group_id', $request->booking_group_id))
                ->whereIn('status', ['reserved', 'checked_in'])
                ->update(['status' => 'cancelled']);
        }

        RoomAllocation::create([
            'hotel_room_id'    => $request->room_id,
            'passenger_id'     => $request->passenger_id,
            'booking_group_id' => $request->booking_group_id,
            'status'           => 'reserved',
            'allocated_by'     => user()->id,
        ]);

        // Update passenger's room_allocation_id
        Passenger::where('id', $request->passenger_id)
            ->update(['room_allocation_id' => $request->room_id]);

        return Reply::success(__('messages.recordSaved'));
    }

    /**
     * Remove a passenger from a room.
     */
    public function remove(Request $request)
    {
        $editPermission = user()->permission('edit_booking');
        abort_403(!in_array($editPermission, ['all', 'added']));

        $request->validate([
            'allocation_id' => 'required|exists:room_allocations,id',
            'passenger_id'  => 'required|exists:passengers,id',
        ]);

        RoomAllocation::where('id', $request->allocation_id)
            ->where('passenger_id', $request->passenger_id)
            ->update(['status' => 'cancelled']);

        Passenger::where('id', $request->passenger_id)
            ->update(['room_allocation_id' => null]);

        return Reply::success(__('messages.deleteSuccess'));
    }
}
