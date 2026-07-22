<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransportRate extends BaseModel
{
    protected $guarded = ['id'];

    public function transporter(): BelongsTo
    {
        return $this->belongsTo(Transporter::class, 'transporter_id');
    }

    public function transportType(): BelongsTo
    {
        return $this->belongsTo(TransportType::class, 'transport_type_id');
    }

    public function route(): BelongsTo
    {
        return $this->belongsTo(TransportRoute::class, 'route_id');
    }
}
