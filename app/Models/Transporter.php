<?php

namespace App\Models;

use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Transporter extends BaseModel
{
    use HasCompany;

    protected $fillable = [
        'company_id',
        'name',
        'phone',
        'email',
        'city',
        'contact_person',
        'notes',
    ];

    public function accounts(): HasMany
    {
        return $this->hasMany(TransporterAccount::class, 'transporter_id');
    }

    public function rates(): HasMany
    {
        return $this->hasMany(TransportRate::class, 'transporter_id');
    }
}
