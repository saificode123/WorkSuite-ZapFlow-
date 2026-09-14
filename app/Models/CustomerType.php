<?php

namespace App\Models;

use App\Traits\HasCompany;

class CustomerType extends BaseModel
{
    use HasCompany;

    protected $fillable = [
        'company_id',
        'name',
        'description',
        'config_json',
    ];

    protected $casts = [
        'config_json' => 'array',
    ];
}
