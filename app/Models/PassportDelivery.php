<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PassportDelivery extends BaseModel
{
    protected $guarded = ['id'];

    public function passenger(): BelongsTo
    {
        return $this->belongsTo(Passenger::class, 'passenger_id');
    }

    public function handledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handled_by');
    }
}
