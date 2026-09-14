<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HotelRate extends BaseModel
{
    protected $fillable = [
        'hotel_id',
        'room_type',
        'season',
        'tariff',
        'sell_rate',
        'valid_from',
        'valid_to',
        'added_by',
    ];

    public function hotel(): BelongsTo
    {
        return $this->belongsTo(Hotel::class, 'hotel_id');
    }
}
