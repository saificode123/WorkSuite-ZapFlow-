<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Currency;
use App\Models\GlobalSetting;
use App\Models\PushNotificationSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class BookingScopingTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Bootstrap the shared scaffolding the BookingController middleware
     * assumes exists: permission_types, global_setting, a company, and a
     * role with the view_booking permission so user()->permission() works.
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Clear cached helpers so actingAs() picks up the correct user context.
        session()->forget('user');
        session()->forget('user_roles');
        cache()->flush();

        DB::table('permission_types')->insert([
            ['id' => 1, 'name' => 'added'],
            ['id' => 2, 'name' => 'owned'],
            ['id' => 3, 'name' => 'both'],
            ['id' => 4, 'name' => 'all'],
            ['id' => 5, 'name' => 'none'],
        ]);

        PushNotificationSetting::create(['status' => 'inactive']);

        GlobalSetting::firstOrCreate(
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

        $currency = Currency::create([
            'currency_name'   => 'Pakistani Rupee',
            'currency_code'   => 'PKR',
            'currency_symbol' => 'PKR',
            'is_cryptocurrency' => 'no',
            'currency_position' => 'left',
            'no_of_decimal'   => 2,
        ]);

        $this->company = Company::create([
            'company_name'  => 'Test Co',
            'company_email' => 'test@test.test',
            'currency_id'   => $currency->id,
            'date_format'   => 'd-m-Y',
            'time_format'   => 'h:i a',
            'timezone'      => 'UTC',
            'locale'        => 'en',
            'status'        => 'active',
        ]);

        // Module entry (legacy modules table — not used by user_modules(), kept for completeness)
        $bookingsModuleId = DB::table('modules')->insertGetId([
            'module_name' => 'bookings',
            'description' => 'Bookings',
        ]);

        // CompanyObserver auto-creates admin/client/employee roles on company create.
        $this->agentRoleId = DB::table('roles')
            ->where('company_id', $this->company->id)
            ->where('name', 'employee')
            ->value('id');

        DB::table('module_settings')->insert([
            'company_id'   => $this->company->id,
            'module_name'  => 'bookings',
            'type'         => 'employee',
            'status'       => 'active',
        ]);

        // Insert permission row for the bookings module
        $this->viewBookingPermissionId = DB::table('permissions')->insertGetId([
            'name'       => 'view_booking',
            'module_id'  => $bookingsModuleId,
            'is_custom'  => 0,
        ]);

        // The User->permission() helper reads from `user_permissions` (per-user table),
        // not `permission_role`. We assign the permission per-user in the test methods
        // so each agent gets `view_booking` with type `owned` (permission_type_id = 2).
    }

    /** @test */
    public function an_agent_cannot_access_another_agents_booking()
    {
        $agentA = User::factory()->create([
            'company_id'     => $this->company->id,
            'admin_approval' => 1,
        ]);
        $agentB = User::factory()->create([
            'company_id'     => $this->company->id,
            'admin_approval' => 1,
        ]);

        $booking = \App\Models\BookingGroup::factory()->create([
            'customer_id'   => $agentA->id,
            'company_id'    => $this->company->id,
        ]);

        // Assign agents to role (for user_roles() helper) and per-user permission
        DB::table('role_user')->insert([
            ['user_id' => $agentA->id, 'role_id' => $this->agentRoleId],
            ['user_id' => $agentB->id, 'role_id' => $this->agentRoleId],
        ]);
        DB::table('user_permissions')->insert([
            // permission_type_id 2 = 'owned'
            ['user_id' => $agentA->id, 'permission_id' => $this->viewBookingPermissionId, 'permission_type_id' => 2],
            ['user_id' => $agentB->id, 'permission_id' => $this->viewBookingPermissionId, 'permission_type_id' => 2],
        ]);

        $response = $this->actingAs($agentB)->get(route('bookings.show', $booking));

        // With 'owned' permission, agentB should only see bookings where they are the customer.
        // Since this booking belongs to agentA, agentB gets 403.
        $response->assertStatus(403);
    }

    /** @test */
    public function an_agent_can_access_their_own_booking()
    {
        $agentA = User::factory()->create([
            'company_id'     => $this->company->id,
            'admin_approval' => 1,
        ]);

        $booking = \App\Models\BookingGroup::factory()->create([
            'customer_id'   => $agentA->id,
            'company_id'    => $this->company->id,
        ]);

        // Assign agent to role and per-user permission
        DB::table('role_user')->insert([
            ['user_id' => $agentA->id, 'role_id' => $this->agentRoleId],
        ]);
        DB::table('user_permissions')->insert([
            // permission_type_id 2 = 'owned'
            ['user_id' => $agentA->id, 'permission_id' => $this->viewBookingPermissionId, 'permission_type_id' => 2],
        ]);

        $response = $this->actingAs($agentA)->get(route('bookings.show', $booking));

        // With 'owned' permission, agentA can see bookings where they are the customer.
        $response->assertStatus(200);
    }
}
