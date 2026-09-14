<?php

namespace App\Models;

use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CashReceipt extends BaseModel
{
    use HasCompany;

    protected $table = 'cash_receipts';

    protected $fillable = [
        'company_id',
        'account_id',
        'amount',
        'currency_code',
        'date',
        'received_from',
        'reference_no',
        'narration',
        'journal_voucher_id',
        'added_by',
        'last_updated_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    protected $dates = ['date'];

    public function account(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'account_id');
    }

    public function journalVoucher(): BelongsTo
    {
        return $this->belongsTo(JournalVoucher::class, 'journal_voucher_id');
    }

    public function addedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'added_by');
    }
}
