<?php

namespace App\Models;

use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Hotel extends BaseModel
{
    use HasCompany;

    protected $table = 'hotels';
 
    protected $fillable = [
        'company_id',
        'name',
        'city',
        'country',
        'stars',
        'address',
        'phone',
        'email',
        'contact_person',
        'added_by',
        'last_updated_by',
    ];

    public function rates(): HasMany
    {
        return $this->hasMany(HotelRate::class, 'hotel_id');
    }

    public function rooms(): HasMany
    {
        return $this->hasMany(HotelRoom::class, 'hotel_id');
    }

    public function serviceProviders(): BelongsToMany
    {
        return $this->belongsToMany(ServiceProvider::class, 'hotel_service_provider_links', 'hotel_id', 'service_provider_id');
    }

    public function packages(): BelongsToMany
    {
        return $this->belongsToMany(Package::class, 'package_hotels', 'hotel_id', 'package_id');
    }
}
