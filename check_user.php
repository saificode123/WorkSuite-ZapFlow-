<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$k = $app->make(Illuminate\Contracts\Console\Kernel::class);
$k->bootstrap();

$u = App\Models\User::first();
echo "User: " . $u->name . PHP_EOL;
echo "Roles: " . $u->getRoleNames()->implode(',') . PHP_EOL;
echo "Modules: " . App\Helper\user_modules()->implode(',') . PHP_EOL;
