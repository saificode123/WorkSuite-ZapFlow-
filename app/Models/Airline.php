<?php

namespace App\Models;

use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Airline extends BaseModel
{
    use HasCompany;

    protected $fillable = [
        'company_id',
        'name',
        'code',
        'logo',
        'phone',
        'email',
        'website',
    ];

    public function accounts(): HasMany
    {
        return $this->hasMany(AirlineAccount::class, 'airline_id');
    }
}
