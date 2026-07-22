<?php

namespace App\Models;

use App\Traits\HasCompany;

class Discount extends BaseModel
{
    use HasCompany;

    protected $guarded = ['id'];

    protected $dates = ['valid_from', 'valid_to'];
}
