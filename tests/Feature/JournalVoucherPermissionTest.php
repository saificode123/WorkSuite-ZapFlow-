<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Currency;
use App\Models\FinancialYear;
use App\Models\ChartOfAccount;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * JournalVoucherPermissionTest
 *
 * Root causes that previously caused a 500 instead of 403:
 *  1. Factory user had no company_id → company() helper returned false →
 *     AccountBaseController middleware threw when accessing user()->company.
 *  2. admin_approval was null → AccountBaseController redirected to
 *     'account_unverified' before store() was reached.
 *  3. financial_years and chart_of_accounts tables were empty (RefreshDatabase) →
 *     StoreRequest validation failed with 422 before abort_403 fired.
 *
 * Fix: Bootstrap the minimal required company/currency context, set admin_approval,
 * create a financial year and two accounts so validation passes, and confirm
 * that a user with no 'add_journal_voucher' permission gets exactly 403.
 */
class JournalVoucherPermissionTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_user_without_permission_cannot_store_a_voucher()
    {
        // ── Seed permission_types (RefreshDatabase doesn't run seeders) ────────
        DB::table('permission_types')->insert([
            ['id' => 1, 'name' => 'added'],
            ['id' => 2, 'name' => 'owned'],
            ['id' => 3, 'name' => 'both'],
            ['id' => 4, 'name' => 'all'],
            ['id' => 5, 'name' => 'none'],
        ]);

        // ── Bootstrap currency (needed by Company) ───────────────────────────
        $currency = Currency::create([
            'currency_name'   => 'Pakistani Rupee',
            'currency_code'   => 'PKR',
            'currency_symbol' => 'PKR',
            'is_cryptocurrency' => 'no',
            'currency_position' => 'left',
            'no_of_decimal'   => 2,
        ]);

        // ── Bootstrap GlobalSetting (required by CompanyObserver) ────────────
        \App\Models\GlobalSetting::firstOrCreate(
            ['id' => 1],
            [
                'global_app_name'           => 'ZapFlow Test',
                'logo_background_color'     => '#ffffff',
                'header_color'              => '#1D82F5',
                'sidebar_logo_style'        => 'square',
                'locale'                    => 'en',
                'google_recaptcha_status'   => 'deactive',
                'google_recaptcha_v2_status'=> 'deactive',
                'google_recaptcha_v3_status'=> 'deactive',
                'app_debug'                 => 0,
                'currency_key_version'      => 'free',
            ]
        );

        // ── Bootstrap company ────────────────────────────────────────────────
        $company = Company::create([
            'company_name'  => 'Test Co',
            'company_email' => 'test@test.test',
            'currency_id'   => $currency->id,
            'date_format'   => 'd-m-Y',
            'time_format'   => 'h:i a',
            'timezone'      => 'UTC',
            'locale'        => 'en',
            'status'        => 'active',
        ]);

        // ── Create user with company context and admin_approval ──────────────
        $user = User::factory()->create([
            'company_id'     => $company->id,
            'admin_approval' => 1,   // Without this AccountBaseController redirects to account_unverified
        ]);

        // ── Create a real financial year so validation doesn't fail with 422 ─
        $fy = FinancialYear::create([
            'company_id' => $company->id,
            'name'       => 'Test FY',
            'start_date' => '2025-07-01',
            'end_date'   => '2026-06-30',
            'is_closed'  => false,
        ]);

        // ── Create two accounts so the lines array passes validation ─────────
        $accountA = ChartOfAccount::create([
            'company_id'     => $company->id,
            'code'           => '1001',
            'name'           => 'Cash',
            'type'           => 'asset',
            'level'          => 1,
        ]);
        $accountB = ChartOfAccount::create([
            'company_id'     => $company->id,
            'code'           => '4001',
            'name'           => 'Revenue',
            'type'           => 'income',
            'level'          => 1,
        ]);

        // ── POST to store — user has no 'add_journal_voucher' permission ─────
        // Validation will pass (real FY + accounts exist). Then abort_403
        // fires because user()->permission('add_journal_voucher') returns false,
        // not 'all' or 'added'. Expected result: 403 Forbidden.
        $response = $this->actingAs($user)->post(route('journal-vouchers.store'), [
            'financial_year_id' => $fy->id,
            'voucher_number'    => 'JV-TEST-001',
            'date'              => now()->toDateString(),
            'narration'         => 'Test',
            'lines'             => [
                ['account_id' => $accountA->id, 'debit' => 100, 'credit' => 0, 'description' => 'test'],
                ['account_id' => $accountB->id, 'debit' => 0, 'credit' => 100, 'description' => 'test'],
            ],
        ]);

        $response->assertStatus(403);
    }
}

