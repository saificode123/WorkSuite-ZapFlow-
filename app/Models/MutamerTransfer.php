<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MutamerTransfer extends BaseModel
{
    protected $guarded = ['id'];

    public function passenger(): BelongsTo
    {
        return $this->belongsTo(Passenger::class, 'passenger_id');
    }

    public function fromBookingGroup(): BelongsTo
    {
        return $this->belongsTo(BookingGroup::class, 'from_booking_group_id');
    }

    public function toBookingGroup(): BelongsTo
    {
        return $this->belongsTo(BookingGroup::class, 'to_booking_group_id');
    }

    public function transferredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'transferred_by');
    }
}
