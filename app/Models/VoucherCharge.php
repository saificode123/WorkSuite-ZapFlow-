<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VoucherCharge extends BaseModel
{
    protected $guarded = ['id'];

    public function voucher(): BelongsTo
    {
        return $this->belongsTo(Voucher::class, 'voucher_id');
    }
}
