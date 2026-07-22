<?php

namespace App\Models;

use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BookingGroup extends BaseModel
{
    use HasCompany;

    protected $guarded = ['id'];

    protected $dates = ['departure_date', 'return_date'];

    public function passengers(): HasMany
    {
        return $this->hasMany(Passenger::class, 'booking_group_id');
    }

    public function charges(): HasMany
    {
        return $this->hasMany(BookingCharge::class, 'booking_group_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class, 'package_id');
    }

    public function iata(): BelongsTo
    {
        return $this->belongsTo(IataRecord::class, 'iata_id');
    }
}
