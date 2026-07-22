<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransporterAccount extends BaseModel
{
    protected $guarded = ['id'];

    public function transporter(): BelongsTo
    {
        return $this->belongsTo(Transporter::class, 'transporter_id');
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'account_id');
    }
}
