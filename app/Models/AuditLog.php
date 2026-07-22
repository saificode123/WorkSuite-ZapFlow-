<?php

namespace App\Models;

class AuditLog extends BaseModel
{
    protected $guarded = ['id'];

    protected $casts = [
        'before' => 'array',
        'after' => 'array',
    ];
}
