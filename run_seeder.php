<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Running DemoDataSeeder...\n";
Artisan::call('db:seed', ['--class' => \Database\Seeders\DemoDataSeeder::class]);
echo Artisan::output();
echo "Done.\n";
