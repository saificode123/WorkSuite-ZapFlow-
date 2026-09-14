<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Services\Reports\UmrahPlCalculator;
use Illuminate\Support\Facades\DB;

$pdo = DB::connection()->getDatabaseName();
echo "Database: $pdo\n\n";

$groups = DB::table('booking_groups')->select('id', 'group_name')->get();
$calc = new UmrahPlCalculator();

foreach ($groups as $g) {
    $charges = DB::table('booking_charges')->where('booking_group_id', $g->id)->sum('amount');
    $costs = DB::table('travel_payments')
        ->where('booking_group_id', $g->id)
        ->where('payment_direction', 'make')
        ->where('status', 'posted')
        ->sum('amount');
    $pl = $calc->calculate($g->id);
    $ref = $g->group_name;
    echo "Group {$g->id} ({$ref}):\n";
    echo "  Hand-check charges: $charges | make/posted payments: $costs | margin: " . ($charges - $costs) . "\n";
    echo "  Calculator: revenue={$pl->revenue} cost={$pl->cost} margin={$pl->margin}\n\n";
}
