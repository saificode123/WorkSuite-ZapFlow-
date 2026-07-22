<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IataAccount extends BaseModel
{
    protected \ = ['id'];

    public function iataRecord(): BelongsTo
    {
        return \->belongsTo(IataRecord::class, 'iata_id');
    }

    public function account(): BelongsTo
    {
        return \->belongsTo(ChartOfAccount::class, 'account_id');
    }
}
