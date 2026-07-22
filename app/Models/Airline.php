<?php

namespace App\Models;

use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Airline extends BaseModel
{
    use HasCompany;

    protected $guarded = ['id'];

    public function accounts(): HasMany
    {
        return $this->hasMany(AirlineAccount::class, 'airline_id');
    }
}
