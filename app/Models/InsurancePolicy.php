<?php

namespace App\Models;

use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InsurancePolicy extends BaseModel
{
    use HasCompany;

    protected $table = 'insurance_policies';
 
    protected $fillable = [
        'company_id',
        'provider_name',
        'policy_type',
        'policy_number',
        'rate',
        'currency_code',
        'coverage_summary',
        'terms_conditions',
        'valid_from',
        'valid_to',
        'is_active',
        'added_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'rate' => 'decimal:2',
    ];

    protected $dates = ['valid_from', 'valid_to'];

    public function sales(): HasMany
    {
        return $this->hasMany(InsuranceSale::class, 'policy_id');
    }
}
