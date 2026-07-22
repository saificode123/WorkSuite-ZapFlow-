<?php

namespace App\Models;

use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ServiceProvider extends BaseModel
{
    use HasCompany;

    protected $guarded = ['id'];

    public function accounts(): HasMany
    {
        return $this->hasMany(ServiceProviderAccount::class, 'service_provider_id');
    }

    public function hotels(): BelongsToMany
    {
        return $this->belongsToMany(Hotel::class, 'hotel_service_provider_links', 'service_provider_id', 'hotel_id');
    }

    public function iataRecords(): BelongsToMany
    {
        return $this->belongsToMany(IataRecord::class, 'iata_service_provider_links', 'service_provider_id', 'iata_id');
    }
}
