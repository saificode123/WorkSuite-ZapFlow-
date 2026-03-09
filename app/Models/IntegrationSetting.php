<?php
// Create file: app/Models/IntegrationSetting.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IntegrationSetting extends Model
{
    protected $fillable = [
        'company_id',
        'jira_status',
        'jira_host',
        'jira_user',
        'jira_api_key',
        'bugherd_status',
        'bugherd_base',
        'bugherd_api_key',
        'bugherd_webhook_token',
        'xero_status',
        'xero_client_id',
        'xero_client_secret',
        'xero_redirect_uri',
        'xero_scopes',
    ];

    protected $casts = [
        'jira_status' => 'boolean',
        'bugherd_status' => 'boolean',
        'xero_status' => 'boolean',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
