<?php

namespace App\Models;

use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Package extends BaseModel
{
    use HasCompany;

    protected $fillable = [
        'company_id',
        'name',
        'type',
        'duration_days',
        'price',
        'cost_price',
        'markup_percentage',
        'is_price_auto_calculated',
        'description',
        'transporter_id',
        'visa_company_id',
        'added_by',
        'last_updated_by',
    ];

    public function hotels(): BelongsToMany
    {
        return $this->belongsToMany(Hotel::class, 'package_hotels', 'package_id', 'hotel_id');
    }

    public function transporter(): BelongsTo
    {
        return $this->belongsTo(Transporter::class, 'transporter_id');
    }

    public function visaCompany(): BelongsTo
    {
        return $this->belongsTo(VisaCompany::class, 'visa_company_id');
    }
}

