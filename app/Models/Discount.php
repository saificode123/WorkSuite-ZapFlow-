<?php

namespace App\Models;

use App\Traits\HasCompany;

class Discount extends BaseModel
{
    use HasCompany;

    protected $fillable = [
        'company_id',
        'code',
        'name',
        'type',
        'value',
        'valid_from',
        'valid_to',
        'is_active',
    ];

    protected $dates = ['valid_from', 'valid_to'];
}
