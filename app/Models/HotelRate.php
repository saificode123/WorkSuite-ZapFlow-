<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HotelRate extends BaseModel
{
    protected $guarded = ['id'];

    public function hotel(): BelongsTo
    {
        return $this->belongsTo(Hotel::class, 'hotel_id');
    }
}
