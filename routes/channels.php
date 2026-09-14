<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('chat', function ($user) {
    return auth()->user();
});

// Per-company private channels — only users in the same tenant may subscribe.
// Admins always pass; otherwise the user must have an account on the same company.
Broadcast::channel('travel-payments.{companyId}', function ($user, $companyId) {
    if (!$user) return false;
    // Admin role bypasses tenant scoping.
    if (method_exists($user, 'hasRole') && $user->hasRole('admin')) return true;
    return (int) $user->company_id === (int) $companyId;
});

Broadcast::channel('visa-pipeline.{companyId}', function ($user, $companyId) {
    if (!$user) return false;
    if (method_exists($user, 'hasRole') && $user->hasRole('admin')) return true;
    return (int) $user->company_id === (int) $companyId;
});

Broadcast::channel('vouchers.{companyId}', function ($user, $companyId) {
    if (!$user) return false;
    if (method_exists($user, 'hasRole') && $user->hasRole('admin')) return true;
    return (int) $user->company_id === (int) $companyId;
});
