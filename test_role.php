<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
echo "Step 1: try to insert via Role::create('super_admin'):\n";
try {
    $r = \App\Models\Role::create([
        'name' => 'super_admin',
        'company_id' => 1,
        'display_name' => 'X',
    ]);
    echo "Saved id={$r->id} name={$r->name}\n";
} catch (\Throwable $e) {
    echo "Failed: " . $e->getMessage() . "\n";
}
