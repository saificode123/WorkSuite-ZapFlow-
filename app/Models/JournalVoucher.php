<?php

namespace App\Models;

use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JournalVoucher extends BaseModel
{
    use HasCompany;

    protected $guarded = ['id'];

    protected $dates = ['date'];

    public function financialYear(): BelongsTo
    {
        return $this->belongsTo(FinancialYear::class, 'financial_year_id');
    }

    public function lines(): HasMany
    {
        return $this->hasMany(JournalVoucherLine::class, 'journal_voucher_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
