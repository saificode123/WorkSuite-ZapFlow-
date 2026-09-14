<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingCharge extends BaseModel
{
    protected $fillable = [
        'booking_group_id',
        'charge_type',
        'amount',
        'account_id',
        'notes',
    ];

    public function bookingGroup(): BelongsTo
    {
        return $this->belongsTo(BookingGroup::class, 'booking_group_id');
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'account_id');
    }
}
