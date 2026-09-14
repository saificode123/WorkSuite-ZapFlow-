<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = App\Models\User::first();
Auth::login($user);

echo 'Is Admin: ' . ($user->hasRole('admin') ? 'Yes' : 'No') . PHP_EOL;
echo 'Modules: ' . json_encode($user->modules) . PHP_EOL;
echo 'Permission view_travel_payment: ' . $user->permission('view_travel_payment') . PHP_EOL;
