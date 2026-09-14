<?php

namespace Tests\Unit;

use App\Services\Reports\UmrahPlCalculator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * UmrahPlCalculatorTest
 *
 * Tests UmrahPlCalculator::calculate() with known fixture data inserted
 * directly via DB::table() — no seeder dependency, runs in seconds.
 *
 * Fixture:
 *   booking_group_id = 999  (synthetic, never collides with real data)
 *
 *   booking_charges:
 *     - 1000.00  (revenue — customer charge)
 *
 *   travel_payments (direction='make', status='posted'):
 *     - 600.00   (cost — vendor payment)
 *
 *   Expected P&L:
 *     Revenue = 1000.00
 *     Cost    =  600.00
 *     Margin  =  400.00  (400/1000 = 40%)
 *
 * BG-2026-001 expected P&L (from DemoDataSeeder, hand-verified vs TEST_ACCOUNTS.md):
 *   Revenue = 280,000   (1× booking_charge 'package_fee', 4 pax @ 70,000 each)
 *   Cost    =  80,000   (1× travel_payment 'make', hotel advance JV-DEMO-002)
 *   Margin  = 200,000
 */
class UmrahPlCalculatorTest extends TestCase
{
    use RefreshDatabase;

    private const FIXTURE_GROUP_ID = 999;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedPermissionTypes();

        // Disable FK checks so we can insert fixture rows with synthetic IDs
        // (e.g. booking_group_id=999) without creating full object trees.
        // Re-enabled in tearDown. RefreshDatabase already handles cleanup.
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
    }

    protected function tearDown(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
        parent::tearDown();
    }

    // ── Helper: insert permission_types so CompanyObserver doesn't fail ──────

    private function seedPermissionTypes(): void
    {
        DB::table('permission_types')->insertOrIgnore([
            ['id' => 1, 'name' => 'added'],
            ['id' => 2, 'name' => 'owned'],
            ['id' => 3, 'name' => 'both'],
            ['id' => 4, 'name' => 'all'],
            ['id' => 5, 'name' => 'none'],
        ]);
    }

    // ── Helper: insert fixture rows directly via DB (no Eloquent observers) ──

    private function insertFixture(): void
    {
        $now = now()->toDateTimeString();

        DB::table('booking_charges')->insert([
            'booking_group_id' => self::FIXTURE_GROUP_ID,
            'charge_type'      => 'package_fee',
            'amount'           => 1000.00,
            'added_by'         => 0,
            'created_at'       => $now,
            'updated_at'       => $now,
        ]);

        DB::table('travel_payments')->insert([
            'company_id'           => 0,
            'booking_group_id'     => self::FIXTURE_GROUP_ID,
            'payment_direction'    => 'make',
            'amount'               => 600.00,
            'currency_code'        => 'PKR',
            'exchange_rate'        => 1.0,
            'amount_base_currency' => 600.00,
            'payment_date'         => now()->toDateString(),
            'status'               => 'posted',
            'added_by'             => 0,
            'created_at'           => $now,
            'updated_at'           => $now,
        ]);

        // Also insert a 'receive' payment — must NOT be counted as cost.
        DB::table('travel_payments')->insert([
            'company_id'           => 0,
            'booking_group_id'     => self::FIXTURE_GROUP_ID,
            'payment_direction'    => 'receive',
            'amount'               => 500.00,
            'currency_code'        => 'PKR',
            'exchange_rate'        => 1.0,
            'amount_base_currency' => 500.00,
            'payment_date'         => now()->toDateString(),
            'status'               => 'posted',
            'added_by'             => 0,
            'created_at'           => $now,
            'updated_at'           => $now,
        ]);
    }

    // ── Tests ────────────────────────────────────────────────────────────────

    /** @test */
    public function it_returns_correct_pl_for_known_fixture_data(): void
    {
        $this->insertFixture();

        $calculator = new UmrahPlCalculator();
        $pl = $calculator->calculate(self::FIXTURE_GROUP_ID);

        // Revenue: only booking_charges, NOT payments
        $this->assertEquals(1000.0, $pl->revenue,
            'Revenue must equal sum of booking_charges.amount (1000)');

        // Cost: only travel_payments WHERE direction=make AND status=posted
        // The 'receive' payment (500) must be excluded.
        $this->assertEquals(600.0, $pl->cost,
            'Cost must equal sum of make-direction posted travel_payments (600), not including receive payments');

        // Margin: revenue - cost
        $this->assertEquals(400.0, $pl->margin,
            'Margin = 1000 - 600 = 400');
    }

    /** @test */
    public function it_returns_correct_margin_percent(): void
    {
        $this->insertFixture();

        $calculator = new UmrahPlCalculator();
        $pl = $calculator->calculate(self::FIXTURE_GROUP_ID);

        // 400 / 1000 = 40%
        $this->assertEquals(40.0, $pl->marginPercent(),
            '400 margin on 1000 revenue = 40.0%');
    }

    /** @test */
    public function it_returns_zero_pl_for_non_existent_group(): void
    {
        $calculator = new UmrahPlCalculator();
        $pl = $calculator->calculate(99999);

        $this->assertEquals(0.0, $pl->revenue);
        $this->assertEquals(0.0, $pl->cost);
        $this->assertEquals(0.0, $pl->margin);
        $this->assertEquals(0.0, $pl->marginPercent(), 'Division by zero guard: marginPercent must be 0.0 when revenue = 0');
    }

    /** @test */
    public function it_excludes_draft_payments_from_cost(): void
    {
        $now = now()->toDateTimeString();

        // Posted payment — should be counted
        DB::table('travel_payments')->insert([
            'company_id' => 0, 'booking_group_id' => 888,
            'payment_direction' => 'make', 'amount' => 200.0,
            'currency_code' => 'PKR', 'exchange_rate' => 1.0, 'amount_base_currency' => 200.0,
            'payment_date' => now()->toDateString(), 'status' => 'posted', 'added_by' => 0,
            'created_at' => $now, 'updated_at' => $now,
        ]);

        // Draft payment — must NOT be counted
        DB::table('travel_payments')->insert([
            'company_id' => 0, 'booking_group_id' => 888,
            'payment_direction' => 'make', 'amount' => 9999.0,
            'currency_code' => 'PKR', 'exchange_rate' => 1.0, 'amount_base_currency' => 9999.0,
            'payment_date' => now()->toDateString(), 'status' => 'draft', 'added_by' => 0,
            'created_at' => $now, 'updated_at' => $now,
        ]);

        $calculator = new UmrahPlCalculator();
        $pl = $calculator->calculate(888);

        $this->assertEquals(200.0, $pl->cost,
            'Draft payments (status=draft) must not be included in cost — only posted payments count');
    }
}
