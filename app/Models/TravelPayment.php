<?php

namespace App\Models;

use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TravelPayment extends BaseModel
{
    use HasCompany;

    protected $table = 'travel_payments';
    protected $guarded = ['id'];

    protected $fillable = [
        'company_id',
        'payment_direction',
        'party_user_id',
        'debit_account_id',
        'credit_account_id',
        'amount',
        'currency_code',
        'exchange_rate',
        'amount_base_currency',
        'payment_date',
        'payment_method',
        'reference_no',
        'cheque_no',
        'narration',
        'booking_group_id',
        'journal_voucher_id',
        'status',
        'added_by',
        'last_updated_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'amount_base_currency' => 'decimal:2',
        'exchange_rate' => 'decimal:6',
    ];

    protected $dates = ['payment_date'];

    public function partyUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'party_user_id');
    }

    public function debitAccount(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'debit_account_id');
    }

    public function creditAccount(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'credit_account_id');
    }

    public function bookingGroup(): BelongsTo
    {
        return $this->belongsTo(BookingGroup::class, 'booking_group_id');
    }

    public function journalVoucher(): BelongsTo
    {
        return $this->belongsTo(JournalVoucher::class, 'journal_voucher_id');
    }

    public function addedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'added_by');
    }

    public function isReceive(): bool
    {
        return $this->payment_direction === 'receive';
    }

    public function isMake(): bool
    {
        return $this->payment_direction === 'make';
    }
}
