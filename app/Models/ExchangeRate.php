<?php

namespace App\Models;

use App\Traits\HasCompany;

class ExchangeRate extends BaseModel
{
    use HasCompany;

    protected $guarded = ['id'];

    protected $dates = ['effective_date'];
}
