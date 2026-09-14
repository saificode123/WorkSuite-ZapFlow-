<?php
define("LARAVEL_START", microtime(true));
require "vendor/autoload.php";
$app = require "bootstrap/app.php";
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

// Check teams table
$teamCount = DB::table("teams")->count();
echo "Teams count: $teamCount\n";

if ($teamCount == 0) {
    DB::table("teams")->insert([
        "id" => 1,
        "team_name" => "Management",
        "company_id" => 1,
        "created_at" => now(),
        "updated_at" => now(),
    ]);
    echo "Created Management team (id=1)\n";
}

// Check designations
$desCount = DB::table("designations")->count();
echo "Designations count: $desCount\n";

if ($desCount == 0) {
    DB::table("designations")->insert([
        "id" => 1,
        "name" => "Director",
        "company_id" => 1,
        "created_at" => now(),
        "updated_at" => now(),
    ]);
    echo "Created Director designation (id=1)\n";
}

// Now create employee_details for admin if missing
$adminId = App\Models\User::where("email", "admin@example.com")->value("id");
$hasEmp = DB::table("employee_details")->where("user_id", $adminId)->exists();
echo "Admin user ID: $adminId | Has employee_details: " . ($hasEmp ? "yes" : "no") . "\n";

if (!$hasEmp && $adminId) {
    DB::table("employee_details")->insert([
        "user_id"        => $adminId,
        "company_id"     => 1,
        "employee_id"    => "SYS-ADMIN",
        "hourly_rate"    => 0,
        "department_id"  => 1,
        "designation_id" => 1,
        "joining_date"   => date("Y-m-d H:i:s", strtotime("-1 year")),
        "marital_status" => "single",
        "created_at"     => now(),
        "updated_at"     => now(),
    ]);
    echo "Employee details created for admin\n";
}

echo "Ready for DemoDataSeeder\n";