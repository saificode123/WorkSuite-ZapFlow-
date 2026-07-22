<?php

namespace App\Models;

use App\Traits\HasCompany;

class CustomerType extends BaseModel
{
    use HasCompany;

    protected $guarded = ['id'];

    protected $casts = [
        'config_json' => 'array',
    ];
}
