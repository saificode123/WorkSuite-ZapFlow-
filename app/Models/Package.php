<?php

namespace App\Models;

use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Package extends BaseModel
{
    use HasCompany;

    protected $guarded = ['id'];

    public function hotels(): BelongsToMany
    {
        return $this->belongsToMany(Hotel::class, 'package_hotels', 'package_id', 'hotel_id');
    }
}
