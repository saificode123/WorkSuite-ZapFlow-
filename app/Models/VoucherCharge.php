<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VoucherCharge extends BaseModel
{
    protected $fillable = [
        'voucher_id',
        'charge_type',
        'amount',
        'account_id',
        'notes',
    ];

    public function voucher(): BelongsTo
    {
        return $this->belongsTo(Voucher::class, 'voucher_id');
    }
}
