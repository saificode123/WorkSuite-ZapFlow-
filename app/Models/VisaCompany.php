<?php

namespace App\Models;

use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VisaCompany extends BaseModel
{
    use HasCompany;

    protected $table = 'visa_companies';

    protected $guarded = ['id'];

    protected $fillable = [
        'name',
        'logo_path',
        'voucher_footer_text',
        'voucher_footer_image',
        'approval_sale_rate',
        'approval_cost_rate',
        'contact_person',
        'phone',
        'email',
        'address',
        'added_by',
        'last_updated_by',
    ];
}

