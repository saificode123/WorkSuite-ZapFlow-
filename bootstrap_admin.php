<?php
define("LARAVEL_START", microtime(true));
require "vendor/autoload.php";
$app = require "bootstrap/app.php";
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Role;
use App\Models\Company;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

// Get first company
$company = Company::first();
echo "Company ID: " . $company->id . " | Name: " . $company->company_name . "\n";

// Get admin role
$adminRole = Role::where("name", "admin")->where("company_id", $company->id)->first();
echo "Admin role: " . ($adminRole ? $adminRole->id : "NOT FOUND") . "\n";

// Check if admin exists
$admin = User::where("email", "admin@example.com")->first();
if ($admin) {
    echo "admin@example.com already exists (ID: " . $admin->id . ")\n";
} else {
    $admin = new User();
    $admin->name = "System Admin";
    $admin->email = "admin@example.com";
    $admin->password = Hash::make("123456");
    $admin->company_id = $company->id;
    $admin->gender = "male";
    $admin->save();
    echo "Created admin@example.com (ID: " . $admin->id . ")\n";

    // Employee details
    try {
        App\Models\EmployeeDetails::create([
            "user_id"        => $admin->id,
            "company_id"     => $company->id,
            "employee_id"    => "SYS-ADMIN",
            "hourly_rate"    => 0,
            "department_id"  => 1,
            "designation_id" => 1,
            "joining_date"   => date("Y-m-d H:i:s", strtotime("-1 year")),
            "marital_status" => "single",
        ]);
        echo "Employee details created\n";
    } catch (Exception $e) {
        echo "Employee details error (non-fatal): " . $e->getMessage() . "\n";
    }

    if ($adminRole) {
        DB::table("role_user")->insertOrIgnore([
            "user_id" => $admin->id,
            "role_id" => $adminRole->id,
        ]);
        echo "Admin role assigned\n";
    }
}

echo "Done. Total users: " . User::count() . "\n";