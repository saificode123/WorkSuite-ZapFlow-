<?php

namespace App\Models;

use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class IataRecord extends BaseModel
{
    use HasCompany;

    protected $fillable = [
        'company_id',
        'iata_number',
        'agency_name',
        'contact_person',
        'phone',
        'email',
        'address',
    ];

    public function accounts(): HasMany
    {
        return $this->hasMany(IataAccount::class, 'iata_id');
    }

    public function serviceProviders(): BelongsToMany
    {
        return $this->belongsToMany(ServiceProvider::class, 'iata_service_provider_links', 'iata_id', 'service_provider_id');
    }
}
