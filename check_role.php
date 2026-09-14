<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
echo "super_admin exists: " . var_export(\App\Models\Role::where('name', 'super_admin')->where('company_id', 1)->exists(), true) . PHP_EOL;
echo "super-admin exists: " . var_export(\App\Models\Role::where('name', 'super-admin')->where('company_id', 1)->exists(), true) . PHP_EOL;
echo "Try updateOrCreate:\n";
try {
    $r = \App\Models\Role::updateOrCreate(
        ['name' => 'super_admin', 'company_id' => 1],
        ['display_name' => 'Test', 'description' => 'test']
    );
    echo "Success id={$r->id} name={$r->name}\n";
} catch (\Throwable $e) {
    echo "Failed: " . $e->getMessage() . "\n";
}
