<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class JournalVoucherLine extends Model
{
    protected $fillable = [
        'journal_voucher_id',
        'account_id',
        'debit',
        'credit',
        'description',
    ];

    public function journalVoucher(): BelongsTo
    {
        return $this->belongsTo(JournalVoucher::class, 'journal_voucher_id');
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'account_id');
    }
}
