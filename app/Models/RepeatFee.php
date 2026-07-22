<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RepeatFee extends BaseModel
{
    protected $guarded = ['id'];

    public function passenger(): BelongsTo
    {
        return $this->belongsTo(Passenger::class, 'passenger_id');
    }
}
