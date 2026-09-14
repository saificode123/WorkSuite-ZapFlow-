<?php

namespace App\Models;

class TransportRoute extends BaseModel
{
    protected $table = 'transport_routes';
    protected $fillable = [
        'from_city',
        'to_city',
        'distance_km',
    ];
}
