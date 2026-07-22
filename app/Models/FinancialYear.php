<?php

namespace App\Models;

use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FinancialYear extends BaseModel
{
    use HasCompany;

    protected $guarded = ['id'];

    protected $dates = ['start_date', 'end_date'];

    public function journalVouchers(): HasMany
    {
        return $this->hasMany(JournalVoucher::class, 'financial_year_id');
    }

    public function accountOpenings(): HasMany
    {
        return $this->hasMany(AccountOpening::class, 'financial_year_id');
    }
}
