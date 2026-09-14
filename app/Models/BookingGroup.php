<?php

namespace App\Models;

use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BookingGroup extends BaseModel
{
    use HasCompany;
    use HasFactory;

    protected $fillable = [
        'company_id',
        'group_no',
        'group_name',
        'group_leader',
        'customer_id',
        'package_id',
        'iata_id',
        'visa_company_id',
        'departure_date',
        'return_date',
        'notes',
        'status',
        'total_pax',
        'added_by',
        'last_updated_by',
    ];

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

    public function travelPayments(): HasMany
    {
        return $this->hasMany(TravelPayment::class, 'booking_group_id');
    }

    public function vouchers(): HasMany
    {
        return $this->hasMany(Voucher::class, 'booking_group_id');
    }
}
