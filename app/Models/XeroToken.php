<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class XeroToken extends Model
{
    protected $fillable = [
        'company_id',
        'access_token',
        'refresh_token',
        'expires_in',
        'token_expires_at',
        'tenant_id',
        'tenant_name',
    ];

    protected $casts = [
        'token_expires_at' => 'datetime',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function isTokenExpired()
    {
        return $this->token_expires_at->isPast();
    }
}
