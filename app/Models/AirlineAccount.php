<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AirlineAccount extends BaseModel
{
    protected $guarded = ['id'];

    public function airline(): BelongsTo
    {
        return $this->belongsTo(Airline::class, 'airline_id');
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'account_id');
    }
}
