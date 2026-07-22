<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class IntegrationSetting extends Model
{
    protected $fillable = [
        'company_id',
        'jira_status', 'jira_host', 'jira_user', 'jira_api_key',
        'bugherd_status', 'bugherd_base', 'bugherd_api_key', 'bugherd_webhook_token',
        'xero_status', 'xero_client_id', 'xero_client_secret', 'xero_redirect_uri', 'xero_scopes',
        'ticketing_implementation', 'ticketing_credentials',
        'visa_tracking_implementation', 'visa_tracking_credentials',
        'iata_lookup_implementation', 'iata_lookup_credentials',
        'nusuk_import_implementation', 'nusuk_import_credentials',
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

    public function getDecryptedCredentials(string $field): ?array
    {
        $value = $this->{$field};
        if (!$value) return null;
        try {
            return json_decode(Crypt::decryptString($value), true);
        } catch (\Exception $e) {
            return null;
        }
    }
}
