<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Hash;
use App\Models\User;

$accounts = [
    'superadmin@zapflow.test',
    'admin@zapflow.test',
    'accountant@zapflow.test',
    'visaofficer@zapflow.test',
    'opsstaff@zapflow.test',
    'ticketing@zapflow.test',
    'agent1@zapflow.test',
    'agent2@zapflow.test',
    'subagent1@zapflow.test',
    'customer@zapflow.test',
];

$password = 'Password123!';
echo "Login verification (Hash::check against DB)\n";
echo str_repeat('-', 60) . "\n";

foreach ($accounts as $email) {
    $user = User::where('email', $email)->first();
    if (!$user) {
        echo "FAIL  $email — user not found in DB\n";
        continue;
    }
    $ok = Hash::check($password, $user->password);
    $roles = $user->roles()->pluck('name')->implode(',') ?: '(no roles)';
    echo ($ok ? 'PASS' : 'FAIL') . "  $email  roles=[$roles]  admin_approval={$user->admin_approval}\n";
}
