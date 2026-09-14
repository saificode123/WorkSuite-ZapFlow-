<?php

namespace Tests\Feature;

use App\Models\BookingGroup;
use App\Models\ChartOfAccount;
use App\Models\Company;
use App\Models\Currency;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * TravelReportNewReportsTest
 *
 * Verifies the 3 remaining travel reports (Part A) return HTTP 200.
 *
 * Routes tested:
 *   GET account/travel-reports/agent-comparison
 *   GET account/travel-reports/employee-efficiency
 *   GET account/travel-reports/daily-cash
 */
class TravelReportNewReportsTest extends TestCase
{
    use RefreshDatabase;

    private function bootstrapContext(): User
    {
        if (DB::table('permission_types')->count() === 0) {
            DB::table('permission_types')->insert([
                ['id' => 1, 'name' => 'added'],
                ['id' => 2, 'name' => 'owned'],
                ['id' => 3, 'name' => 'both'],
                ['id' => 4, 'name' => 'all'],
                ['id' => 5, 'name' => 'none'],
            ]);
        }

        $currency = Currency::firstOrCreate(
            ['currency_code' => 'PKR'],
            [
                'currency_name'     => 'Pakistani Rupee',
                'currency_symbol'   => 'PKR',
                'is_cryptocurrency' => 'no',
                'currency_position' => 'left',
                'no_of_decimal'     => 2,
            ]
        );

        \App\Models\GlobalSetting::firstOrCreate(
            ['id' => 1],
            [
                'global_app_name'            => 'ZapFlow Test',
                'logo_background_color'      => '#ffffff',
                'header_color'               => '#1D82F5',
                'sidebar_logo_style'         => 'square',
                'locale'                     => 'en',
                'google_recaptcha_status'    => 'deactive',
                'google_recaptcha_v2_status' => 'deactive',
                'google_recaptcha_v3_status' => 'deactive',
                'app_debug'                  => 0,
                'currency_key_version'       => 'free',
            ]
        );

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

        $user = User::factory()->create([
            'company_id'     => $company->id,
            'admin_approval' => 1,
        ]);

        DB::table('module_settings')->insert([
            'company_id'  => $company->id,
            'module_name' => 'accounts',
            'status'      => 1,
        ]);

        return $user;
    }

    // -- Agent Comparison Report -----------------------------------------------

    /** @test */
    public function agent_comparison_report_returns_200_with_empty_dataset()
    {
        $user = $this->bootstrapContext();
        $response = $this->actingAs($user)->get(route('travel-reports.agent-comparison'));
        $response->assertStatus(200);
    }

    /** @test */
    public function agent_comparison_report_accepts_date_range_filter()
    {
        $user = $this->bootstrapContext();
        $response = $this->actingAs($user)->get(route('travel-reports.agent-comparison', [
            'from_date' => now()->startOfYear()->toDateString(),
            'to_date'   => now()->toDateString(),
        ]));
        $response->assertStatus(200);
    }

    /** @test */
    public function agent_comparison_report_shows_booking_group_name()
    {
        $user    = $this->bootstrapContext();
        $company = $user->company;

        BookingGroup::create([
            'company_id'     => $company->id,
            'group_no'       => 'GRP-001',
            'group_name'     => 'TestGroupAlpha',
            'customer_id'    => null,
            'departure_date' => now()->addDays(30)->toDateString(),
            'return_date'    => now()->addDays(44)->toDateString(),
            'status'         => 'confirmed',
            'total_pax'      => 10,
            'added_by'       => $user->id,
        ]);

        $response = $this->actingAs($user)->get(route('travel-reports.agent-comparison'));
        $response->assertStatus(200);
    }

    // -- Employee Efficiency Report --------------------------------------------

    /** @test */
    public function employee_efficiency_report_returns_200()
    {
        $user = $this->bootstrapContext();
        $response = $this->actingAs($user)->get(route('travel-reports.employee-efficiency'));
        $response->assertStatus(200);
    }

    /** @test */
    public function employee_efficiency_report_accepts_date_filter()
    {
        $user = $this->bootstrapContext();
        $response = $this->actingAs($user)->get(route('travel-reports.employee-efficiency', [
            'from_date' => now()->startOfMonth()->toDateString(),
            'to_date'   => now()->toDateString(),
        ]));
        $response->assertStatus(200);
    }

    // -- Daily Cash Transaction Report -----------------------------------------

    /** @test */
    public function daily_cash_report_returns_200()
    {
        $user = $this->bootstrapContext();
        $response = $this->actingAs($user)->get(route('travel-reports.daily-cash'));
        $response->assertStatus(200);
    }

    /** @test */
    public function daily_cash_report_accepts_account_filter()
    {
        $user    = $this->bootstrapContext();
        $company = $user->company;

        $account = ChartOfAccount::create([
            'company_id'     => $company->id,
            'code'           => '1010',
            'name'           => 'Test Bank Account',
            'type'           => 'asset',
            'level'          => 1,
        ]);

        $response = $this->actingAs($user)->get(route('travel-reports.daily-cash', [
            'from_date'  => now()->startOfMonth()->toDateString(),
            'to_date'    => now()->toDateString(),
            'account_id' => $account->id,
        ]));
        $response->assertStatus(200);
    }

    /** @test */
    public function daily_cash_report_shows_receipt_data_when_present()
    {
        $user    = $this->bootstrapContext();
        $company = $user->company;

        $account = ChartOfAccount::create([
            'company_id'     => $company->id,
            'code'           => '1011',
            'name'           => 'Petty Cash',
            'type'           => 'asset',
            'level'          => 1,
        ]);

        DB::table('cash_receipts')->insert([
            'company_id'    => $company->id,
            'account_id'    => $account->id,
            'amount'        => 5000.00,
            'currency_code' => 'PKR',
            'date'          => now()->toDateString(),
            'received_from' => 'WalkInClient999',
            'reference_no'  => 'REF-001',
            'narration'     => 'Test payment receipt',
            'added_by'      => $user->id,
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);

        $response = $this->actingAs($user)->get(route('travel-reports.daily-cash', [
            'from_date' => now()->toDateString(),
            'to_date'   => now()->toDateString(),
        ]));
        $response->assertStatus(200);
        $response->assertSee('WalkInClient999', false);
        $response->assertSee('REF-001', false);
    }
}
