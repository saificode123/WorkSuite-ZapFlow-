<?php

namespace App\Models;

use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChartOfAccount extends BaseModel
{
    use HasCompany;

    protected $fillable = [
        'company_id',
        'parent_id',
        'name',
        'code',
        'level',
        'type',
        'is_bank_account',
        'currency_code',
        'is_system_account',
        'added_by',
        'last_updated_by',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(ChartOfAccount::class, 'parent_id');
    }

    public function journalVoucherLines(): HasMany
    {
        return $this->hasMany(JournalVoucherLine::class, 'account_id');
    }
}
