<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== GOLDEN PATH DATA CHECK (zapflow_travel) ===\n\n";

$alpha = DB::table('booking_groups')->where('group_name', 'like', '%Alpha%')->first();
$beta  = DB::table('booking_groups')->where('group_name', 'like', '%Beta%')->first();

echo "Step 2 - BG Alpha passengers: " . DB::table('passengers')->where('booking_group_id', $alpha->id ?? 0)->count() . " (expect 4)\n";

$ahmad = DB::table('passengers')->where('first_name', 'Ahmad')->where('family_name', 'Raza')->first();
echo "Step 3 - Ahmad Raza visa_status: " . ($ahmad->visa_status ?? 'N/A') . "\n";

$voucher = DB::table('vouchers')->where('voucher_number', 'VCH-2026-0001')->first();
echo "Step 5/6 - VCH-2026-0001 status: " . ($voucher->status ?? 'missing') . "\n";

$jv = DB::table('journal_vouchers')->where('voucher_number', 'JV-DEMO-003')->first();
if ($jv) {
    $lines = DB::table('journal_voucher_lines')->where('journal_voucher_id', $jv->id)->get();
    $dr = $lines->sum('debit');
    $cr = $lines->sum('credit');
    echo "Step 7 - JV-DEMO-003 Dr=$dr Cr=$cr (expect 280000/280000)\n";
}

if ($alpha) {
    $rev = DB::table('booking_charges')->where('booking_group_id', $alpha->id)->sum('amount');
    $cost = DB::table('travel_payments')->where('booking_group_id', $alpha->id)->where('payment_direction', 'make')->where('status', 'posted')->sum('amount');
    echo "Step 8 - BG Alpha P&L: Revenue=$rev Cost=$cost Margin=" . ($rev - $cost) . " (expect 280000/80000/200000)\n";
}

$agent2 = DB::table('users')->where('email', 'agent2@zapflow.test')->value('id');
$agent2Bookings = DB::table('booking_groups')->where('customer_id', $agent2)->pluck('group_name');
echo "Step 9 - Agent2 bookings: " . $agent2Bookings->implode(', ') . " (expect Beta only)\n";

echo "\nAudit logs count: " . DB::table('audit_logs')->count() . "\n";
