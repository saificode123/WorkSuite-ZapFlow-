<?php

namespace App\Models;

use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JournalVoucher extends BaseModel
{
    use HasCompany;

    protected $fillable = [
        'company_id',
        'financial_year_id',
        'voucher_number',
        'date',
        'narration',
        'total_debit',
        'total_credit',
        'is_balanced',
        'created_by',
    ];

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
