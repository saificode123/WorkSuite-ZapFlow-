<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoomAllocation extends Model
{

    protected $table = 'room_allocations';
    protected $guarded = ['id'];

    protected $fillable = [
        'hotel_room_id',
        'passenger_id',
        'booking_group_id',
        'check_in',
        'check_out',
        'status',
        'notes',
        'allocated_by',
    ];

    protected $dates = ['check_in', 'check_out'];

    public function hotelRoom(): BelongsTo
    {
        return $this->belongsTo(HotelRoom::class, 'hotel_room_id');
    }

    public function passenger(): BelongsTo
    {
        return $this->belongsTo(Passenger::class, 'passenger_id');
    }

    public function bookingGroup(): BelongsTo
    {
        return $this->belongsTo(BookingGroup::class, 'booking_group_id');
    }

    public function allocatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'allocated_by');
    }
}
