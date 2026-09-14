<?php

namespace Database\Seeders;

use App\Enums\MaritalStatus;
use App\Models\Airline;
use App\Models\BookingCharge;
use App\Models\BookingGroup;
use App\Models\ChartOfAccount;
use App\Models\ClientDetails;
use App\Models\Company;
use App\Models\CustomerType;
use App\Models\Discount;
use App\Models\EmployeeDetails;
use App\Models\FinancialYear;
use App\Models\Hotel;
use App\Models\HotelRoom;
use App\Models\InsurancePolicy;
use App\Models\InsuranceSale;
use App\Models\IataRecord;
use App\Models\JournalVoucher;
use App\Models\JournalVoucherLine;
use App\Models\Package;
use App\Models\Passenger;
use App\Models\Permission;
use App\Models\PermissionRole;
use App\Models\PermissionType;
use App\Models\Role;
use App\Models\RoomAllocation;
use App\Models\ServiceProvider;
use App\Models\Transporter;
use App\Models\UniversalSearch;
use App\Models\User;
use App\Models\VisaCompany;
use App\Models\VisaLog;
use App\Models\Voucher;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * DemoDataSeeder
 *
 * Creates all accounts, reference data, and golden-path business records
 * needed to execute the ZapFlow Manual Testing Document without manual entry.
 *
 * USAGE:
 *   php artisan db:seed --class=Database\\Seeders\\DemoDataSeeder
 *
 * Or run as part of fresh seed (it is auto-called from DatabaseSeeder).
 *
 * ALL DEMO ACCOUNTS USE PASSWORD: Password123!
 * NEVER use this password on any real/production system.
 *
 * @safety Refused to run in APP_ENV=production.
 */
class DemoDataSeeder extends Seeder
{
    /** Shared demo password (hashed once, reused for all demo users) */
    private string $demoPassword;

    /** Primary company record */
    private ?Company $company = null;

    /** The admin user created by UsersTableSeeder (acts as "system" actor) */
    private ?User $systemAdmin = null;

    /** Chart of Accounts references (populated during seedChartOfAccounts) */
    private array $accounts = [];

    public function run(): void
    {
        // ── Safety guard ────────────────────────────────────────────────────
        if (App::environment('production')) {
            $this->command->error('DemoDataSeeder REFUSED to run in production environment.');
            return;
        }

        $this->command->info('');
        $this->command->info('╔══════════════════════════════════════════════════╗');
        $this->command->info('║         ZapFlow DemoDataSeeder — Starting        ║');
        $this->command->info('╚══════════════════════════════════════════════════╝');

        // ── Bootstrap prerequisites if running standalone ─────────────────────────
        // When called via --class=, ModulePermissionSeeder may not have run yet.
        $permTypesExist = DB::table('permission_types')->count();
        if ($permTypesExist === 0) {
            $this->command->warn('Permission types missing — running ModulePermissionSeeder first...');
            $this->call(ModulePermissionSeeder::class);
        }

        $countriesExist = DB::table('countries')->count();
        if ($countriesExist === 0) {
            $this->command->warn('Countries missing — running CountriesTableSeeder first...');
            $this->call(CountriesTableSeeder::class);
        }

        $this->demoPassword = Hash::make('Password123!');

        // ── Bootstrap GlobalSetting (required by CompanyObserver) ─────────────
        \App\Models\GlobalSetting::firstOrCreate(
            ['id' => 1],
            [
                'global_app_name'         => 'ZapFlow',
                'logo_background_color'   => '#ffffff',
                'header_color'            => '#1D82F5',
                'sidebar_logo_style'      => 'square',
                'locale'                  => 'en',
                'google_recaptcha_status' => 'deactive',
                'google_recaptcha_v2_status' => 'deactive',
                'google_recaptcha_v3_status' => 'deactive',
                'app_debug'               => 0,
                'currency_key_version'    => 'free',
            ]
        );

        // ── Bootstrap a currency (Company needs currency_id) ─────────────────
        $currency = \App\Models\Currency::firstOrCreate(
            ['currency_code' => 'PKR'],
            [
                'currency_name'     => 'Pakistani Rupee',
                'currency_symbol'   => 'PKR',
                'is_cryptocurrency' => 'no',
                'currency_position' => 'left',
                'no_of_decimal'     => 2,
            ]
        );

        // ── Resolve or bootstrap company ────────────────────────────────────
        $this->company = Company::first();
        if (! $this->company) {
            $this->command->warn('No company found — creating a default demo company...');
            // Minimal company row to satisfy FK constraints
            $this->company = Company::create([
                'company_name'        => 'ZapFlow Demo Travel Agency',
                'company_email'       => 'admin@zapflow.test',
                'company_phone'       => '+92-300-0000000',
                'website'             => 'https://zapflow.test',
                'address'             => 'Lahore, Pakistan',
                'currency_id'         => $currency->id,
                'date_format'         => 'd-m-Y',
                'time_format'         => 'h:i a',
                'timezone'            => 'Asia/Karachi',
                'locale'              => 'en',
                'status'              => 'active',
            ]);
        }

        // ── Resolve or bootstrap system admin ───────────────────────────────
        $this->systemAdmin = User::where('email', 'admin@example.com')->first();
        if (! $this->systemAdmin) {
            $this->command->warn('admin@example.com not found — creating system admin...');
            $adminRole = \App\Models\Role::where('name', 'admin')
                ->where('company_id', $this->company->id)
                ->first();

            $this->systemAdmin = new User();
            $this->systemAdmin->name       = 'System Admin';
            $this->systemAdmin->email      = 'admin@example.com';
            $this->systemAdmin->password   = Hash::make('123456');
            $this->systemAdmin->company_id = $this->company->id;
            $this->systemAdmin->gender     = 'male';
            $this->systemAdmin->save();

            if ($adminRole) {
                DB::table('role_user')->insertOrIgnore([
                    'user_id' => $this->systemAdmin->id,
                    'role_id' => $adminRole->id,
                ]);
            }

            // Ensure FK dependencies exist for employee_details
            $deptId = DB::table('teams')->where('company_id', $this->company->id)->value('id');
            if (! $deptId) {
                $deptId = DB::table('teams')->insertGetId([
                    'team_name'  => 'Management',
                    'company_id' => $this->company->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $desigId = DB::table('designations')->where('company_id', $this->company->id)->value('id');
            if (! $desigId) {
                $desigId = DB::table('designations')->insertGetId([
                    'name'       => 'Director',
                    'company_id' => $this->company->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            try {
                $empDetails = new \App\Models\EmployeeDetails();
                $empDetails->user_id        = $this->systemAdmin->id;
                $empDetails->company_id     = $this->company->id;
                $empDetails->employee_id    = 'SYS-ADMIN';
                $empDetails->hourly_rate    = 0;
                $empDetails->department_id  = $deptId;
                $empDetails->designation_id = $desigId;
                $empDetails->joining_date   = now()->subYear()->toDateTimeString();
                $empDetails->marital_status = \App\Enums\MaritalStatus::Single;
                $empDetails->save();
            } catch (\Exception $e) {
                $this->command->warn('Employee details skipped: ' . $e->getMessage());
            }
        }

        config(['app.seeding' => true]);

        // ── Seed sections (order matters — dependencies flow downward) ───────
        $this->seedCustomerTypes();
        $this->seedChartOfAccounts();
        $this->seedFinancialYear();
        $this->seedHotelsAndRooms();
        $this->seedServiceProvider();
        $this->seedVisaCompany();
        $this->seedTransporter();
        $this->seedIata();
        $this->seedAirline();
        $this->seedPackage();
        $this->seedDiscount();
        $this->seedInsurancePolicies();
        $this->seedCustomRoles();
        $users = $this->seedUsers();
        $this->seedAgent1BookingGroup($users);
        $this->seedAgent2BookingGroup($users);

        config(['app.seeding' => false]);

        $this->printSummary($users);
    }

    // ────────────────────────────────────────────────────────────────────────
    // 1. Customer Types
    // ────────────────────────────────────────────────────────────────────────

    private function seedCustomerTypes(): void
    {
        $this->command->info('[1/14] Seeding Customer Types...');

        $types = [
            ['name' => 'B2B Agent',   'config_json' => ['tier' => 1, 'can_create_sub_agents' => true]],
            ['name' => 'B2C Customer', 'config_json' => ['tier' => 0, 'can_create_sub_agents' => false]],
            ['name' => 'Sub-Agent',   'config_json' => ['tier' => 2, 'can_create_sub_agents' => false]],
        ];

        foreach ($types as $type) {
            CustomerType::firstOrCreate(
                ['name' => $type['name'], 'company_id' => $this->company->id],
                [
                    'config_json' => $type['config_json'],
                    'added_by'    => $this->systemAdmin->id,
                ]
            );
        }
    }

    // ────────────────────────────────────────────────────────────────────────
    // 2. Chart of Accounts (9-account double-entry tree)
    // ────────────────────────────────────────────────────────────────────────

    private function seedChartOfAccounts(): void
    {
        $this->command->info('[2/14] Seeding Chart of Accounts...');

        $companyId = $this->company->id;
        $by        = $this->systemAdmin->id;

        $tree = [
            // ── Assets ──
            ['key' => 'cash',   'code' => '1001', 'name' => 'Cash in Hand',        'type' => 'asset',     'is_bank' => false, 'parent' => null],
            ['key' => 'bank',   'code' => '1002', 'name' => 'Bank Account',         'type' => 'asset',     'is_bank' => true,  'parent' => null],
            ['key' => 'ar',     'code' => '1100', 'name' => 'Accounts Receivable',  'type' => 'asset',     'is_bank' => false, 'parent' => null],
            // ── Liabilities ──
            ['key' => 'ap',     'code' => '2100', 'name' => 'Accounts Payable',     'type' => 'liability', 'is_bank' => false, 'parent' => null],
            // ── Income ──
            ['key' => 'umrah_rev',  'code' => '4001', 'name' => 'Umrah Revenue',    'type' => 'income',    'is_bank' => false, 'parent' => null],
            ['key' => 'ticket_rev', 'code' => '4002', 'name' => 'Ticket Revenue',   'type' => 'income',    'is_bank' => false, 'parent' => null],
            // ── Expenses ──
            ['key' => 'hotel_exp',  'code' => '5001', 'name' => 'Hotel Expense',    'type' => 'expense',   'is_bank' => false, 'parent' => null],
            ['key' => 'trans_exp',  'code' => '5002', 'name' => 'Transport Expense','type' => 'expense',   'is_bank' => false, 'parent' => null],
            ['key' => 'visa_exp',   'code' => '5003', 'name' => 'Visa Expense',     'type' => 'expense',   'is_bank' => false, 'parent' => null],
        ];

        foreach ($tree as $row) {
            $account = ChartOfAccount::firstOrCreate(
                ['code' => $row['code'], 'company_id' => $companyId],
                [
                    'name'           => $row['name'],
                    'type'           => $row['type'],
                    'is_bank_account'=> $row['is_bank'],
                    'level'          => 1,
                    'added_by'       => $by,
                ]
            );
            $this->accounts[$row['key']] = $account;
        }
    }

    // ────────────────────────────────────────────────────────────────────────
    // 3. Financial Year
    // ────────────────────────────────────────────────────────────────────────

    private function seedFinancialYear(): void
    {
        $this->command->info('[3/14] Seeding Financial Year...');

        $fy = FinancialYear::firstOrCreate(
            ['name' => 'FY 2025-2026', 'company_id' => $this->company->id],
            [
                'start_date' => '2025-07-01',
                'end_date'   => '2026-06-30',
                'is_closed'  => false,
                'added_by'   => $this->systemAdmin->id,
            ]
        );

        $this->accounts['financial_year_id'] = $fy->id;
    }

    // ────────────────────────────────────────────────────────────────────────
    // 4. Hotels & Rooms
    // ────────────────────────────────────────────────────────────────────────

    private function seedHotelsAndRooms(): void
    {
        $this->command->info('[4/14] Seeding Hotels & Rooms...');

        $companyId = $this->company->id;
        $by        = $this->systemAdmin->id;

        // Hotel 1 — Makkah
        $hotelMakkah = Hotel::firstOrCreate(
            ['name' => 'Zam Zam Tower Hotel', 'company_id' => $companyId],
            [
                'city'           => 'Makkah',
                'country'        => 'Saudi Arabia',
                'stars'          => 5,
                'address'        => 'Ajyad Street, Makkah 24231',
                'phone'          => '+966-12-5790000',
                'email'          => 'reservations@zamzamtower.sa',
                'contact_person' => 'Ahmed Al-Ghamdi',
                'added_by'       => $by,
            ]
        );

        // Rooms for Hotel 1
        HotelRoom::firstOrCreate(
            ['hotel_id' => $hotelMakkah->id, 'room_number' => 'MK-101'],
            [
                'company_id'         => $companyId,
                'room_type'          => 'double',
                'floor'              => '1',
                'capacity'           => 2,
                'gender_restriction' => 'male',
                'is_available'       => true,
                'added_by'           => $by,
            ]
        );

        HotelRoom::firstOrCreate(
            ['hotel_id' => $hotelMakkah->id, 'room_number' => 'MK-102'],
            [
                'company_id'         => $companyId,
                'room_type'          => 'double',
                'floor'              => '1',
                'capacity'           => 2,
                'gender_restriction' => 'female',
                'is_available'       => true,
                'added_by'           => $by,
            ]
        );

        // Hotel 2 — Madinah
        $hotelMadinah = Hotel::firstOrCreate(
            ['name' => 'Al-Ansar Grand Hotel', 'company_id' => $companyId],
            [
                'city'           => 'Madinah',
                'country'        => 'Saudi Arabia',
                'stars'          => 4,
                'address'        => 'King Faisal Road, Madinah 42311',
                'phone'          => '+966-14-8223000',
                'email'          => 'info@alansar-hotel.sa',
                'contact_person' => 'Omar Al-Ansari',
                'added_by'       => $by,
            ]
        );

        // Rooms for Hotel 2
        HotelRoom::firstOrCreate(
            ['hotel_id' => $hotelMadinah->id, 'room_number' => 'MD-201'],
            [
                'company_id'         => $companyId,
                'room_type'          => 'triple',
                'floor'              => '2',
                'capacity'           => 3,
                'gender_restriction' => 'male',
                'is_available'       => true,
                'added_by'           => $by,
            ]
        );

        HotelRoom::firstOrCreate(
            ['hotel_id' => $hotelMadinah->id, 'room_number' => 'MD-202'],
            [
                'company_id'         => $companyId,
                'room_type'          => 'double',
                'floor'              => '2',
                'capacity'           => 2,
                'gender_restriction' => 'female',
                'is_available'       => true,
                'added_by'           => $by,
            ]
        );

        // Store hotel IDs for later use
        $this->accounts['hotel_makkah_id']  = $hotelMakkah->id;
        $this->accounts['hotel_madinah_id'] = $hotelMadinah->id;
    }

    // ────────────────────────────────────────────────────────────────────────
    // 5. Service Provider
    // ────────────────────────────────────────────────────────────────────────

    private function seedServiceProvider(): void
    {
        $this->command->info('[5/14] Seeding Service Provider...');

        $sp = ServiceProvider::firstOrCreate(
            ['name' => 'Saudi Travel Solutions', 'company_id' => $this->company->id],
            [
                'contact_person' => 'Khalid Al-Rashid',
                'phone'          => '+966-55-1234567',
                'email'          => 'ops@saudits.sa',
                'address'        => 'Riyadh, Saudi Arabia',
                'added_by'       => $this->systemAdmin->id,
            ]
        );

        $this->accounts['service_provider_id'] = $sp->id;
    }

    // ────────────────────────────────────────────────────────────────────────
    // 6. Visa Company
    // ────────────────────────────────────────────────────────────────────────

    private function seedVisaCompany(): void
    {
        $this->command->info('[6/14] Seeding Visa Company...');

        $vc = VisaCompany::firstOrCreate(
            ['name' => 'Al-Amin Visa Services', 'company_id' => $this->company->id],
            [
                'contact_person'     => 'Tariq Mehmood',
                'phone'              => '+92-51-2881234',
                'email'              => 'visas@alamin.pk',
                'address'            => 'Blue Area, Islamabad',
                'approval_sale_rate' => 8500.00,
                'approval_cost_rate' => 6500.00,
                'added_by'           => $this->systemAdmin->id,
            ]
        );

        $this->accounts['visa_company_id'] = $vc->id;
    }

    // ────────────────────────────────────────────────────────────────────────
    // 7. Transporter
    // ────────────────────────────────────────────────────────────────────────

    private function seedTransporter(): void
    {
        $this->command->info('[7/14] Seeding Transporter...');

        $trans = Transporter::firstOrCreate(
            ['name' => 'Hira Transport Co.', 'company_id' => $this->company->id],
            [
                'contact_person' => 'Muhammad Bilal',
                'phone'          => '+92-300-9876543',
                'email'          => 'ops@hiratransport.pk',
                'address'        => 'Lahore, Pakistan',
                'added_by'       => $this->systemAdmin->id,
            ]
        );

        $this->accounts['transporter_id'] = $trans->id;
    }

    // ────────────────────────────────────────────────────────────────────────
    // 8. IATA
    // ────────────────────────────────────────────────────────────────────────

    private function seedIata(): void
    {
        $this->command->info('[8/14] Seeding IATA Record...');

        $iata = IataRecord::firstOrCreate(
            ['name' => 'PIA Lahore Office', 'company_id' => $this->company->id],
            [
                'code'           => 'PIA-LHE',
                'contact_person' => 'Amna Shah',
                'email'          => 'iata@pia.aero',
                'phone'          => '+92-42-35761001',
                'added_by'       => $this->systemAdmin->id,
            ]
        );

        $this->accounts['iata_id'] = $iata->id;
    }

    // ────────────────────────────────────────────────────────────────────────
    // 9. Airline
    // ────────────────────────────────────────────────────────────────────────

    private function seedAirline(): void
    {
        $this->command->info('[9/14] Seeding Airline...');

        $airline = Airline::firstOrCreate(
            ['name' => 'Pakistan International Airlines', 'company_id' => $this->company->id],
            [
                'code'     => 'PK',
                'added_by' => $this->systemAdmin->id,
            ]
        );

        $this->accounts['airline_id'] = $airline->id;
    }

    // ────────────────────────────────────────────────────────────────────────
    // 10. Package
    // ────────────────────────────────────────────────────────────────────────

    private function seedPackage(): void
    {
        $this->command->info('[10/14] Seeding Package...');

        $package = Package::firstOrCreate(
            ['name' => 'Gold Umrah Package 2026', 'company_id' => $this->company->id],
            [
                'description'   => '21-day Umrah package with 5-star Makkah + 4-star Madinah accommodation, visa, transport & guidance.',
                'price'         => 250000.00,
                'duration_days' => 21,
                'added_by'      => $this->systemAdmin->id,
            ]
        );

        // Link both hotels to the package
        $package->hotels()->syncWithoutDetaching([
            $this->accounts['hotel_makkah_id'],
            $this->accounts['hotel_madinah_id'],
        ]);

        $this->accounts['package_id'] = $package->id;
    }

    // ────────────────────────────────────────────────────────────────────────
    // 11. Discount
    // ────────────────────────────────────────────────────────────────────────

    private function seedDiscount(): void
    {
        $this->command->info('[11/14] Seeding Discount...');

        Discount::firstOrCreate(
            [
                'applies_to_type' => Package::class,
                'applies_to_id'   => $this->accounts['package_id'],
                'company_id'      => $this->company->id,
            ],
            [
                'name'          => 'Early Bird 10%',
                'discount_type' => 'percentage',
                'value'         => 10.00,
                'is_active'     => true,
                'valid_from'    => now()->startOfYear()->toDateString(),
                'valid_to'      => now()->addMonths(3)->toDateString(),
                'added_by'      => $this->systemAdmin->id,
            ]
        );
    }

    // ────────────────────────────────────────────────────────────────────────
    // 12. Insurance Policies
    // ────────────────────────────────────────────────────────────────────────

    private function seedInsurancePolicies(): void
    {
        $this->command->info('[12/14] Seeding Insurance Policies...');

        $companyId = $this->company->id;
        $by        = $this->systemAdmin->id;

        $travelPolicy = InsurancePolicy::firstOrCreate(
            ['policy_number' => 'TRV-2026-001', 'company_id' => $companyId],
            [
                'provider_name'    => 'EFU Life Assurance',
                'policy_type'      => 'travel',
                'rate'             => 1500.00,
                'currency_code'    => 'PKR',
                'coverage_summary' => 'Covers trip cancellation, medical emergency, lost baggage up to PKR 500,000.',
                'valid_from'       => now()->toDateString(),
                'valid_to'         => now()->addYear()->toDateString(),
                'is_active'        => true,
                'added_by'         => $by,
            ]
        );

        InsurancePolicy::firstOrCreate(
            ['policy_number' => 'MED-2026-001', 'company_id' => $companyId],
            [
                'provider_name'    => 'Jubilee Insurance',
                'policy_type'      => 'medical',
                'rate'             => 2200.00,
                'currency_code'    => 'PKR',
                'coverage_summary' => 'Medical expenses, hospitalization & repatriation up to PKR 1,000,000.',
                'valid_from'       => now()->toDateString(),
                'valid_to'         => now()->addYear()->toDateString(),
                'is_active'        => true,
                'added_by'         => $by,
            ]
        );

        $this->accounts['travel_policy_id'] = $travelPolicy->id;
    }

    // ────────────────────────────────────────────────────────────────────────
    // 13. Custom Roles
    // ────────────────────────────────────────────────────────────────────────

    private function seedCustomRoles(): void
    {
        $this->command->info('[13/14] Seeding Custom Roles...');

        $companyId = $this->company->id;

        // Role definitions: name => display name
        // NOTE: Role model's name mutator applies str_slug() which converts underscores to hyphens.
        // We use the already-slugged (hyphenated) keys so updateOrCreate and where() queries match
        // what is actually stored in the database.
        $customRoles = [
            'super-admin'     => 'Super Admin',
            'accountant'      => 'Accountant',
            'visa-officer'    => 'Visa Officer',
            'ops-staff'       => 'Ops Staff',
            'ticketing-staff' => 'Ticketing Staff',
        ];

        foreach ($customRoles as $slug => $displayName) {
            // Use DB::table to bypass the name mutator (avoid double-slugging).
            // firstOrCreate on the ORM would slug 'super-admin' again (no-op since it's already slugged),
            // but updateOrCreate is safe here too — the mutator is idempotent for already-hyphenated slugs.
            Role::updateOrCreate(
                ['name' => $slug, 'company_id' => $companyId],
                ['display_name' => $displayName, 'description' => "Demo role: {$displayName}"]
            );
        }

        // Grant super-admin ALL permissions that the admin role has
        $adminRole      = Role::where('name', 'admin')->where('company_id', $companyId)->first();
        $superAdminRole = Role::where('name', 'super-admin')->where('company_id', $companyId)->first();

        if ($adminRole && $superAdminRole) {
            $adminPerms = PermissionRole::where('role_id', $adminRole->id)->get();
            foreach ($adminPerms as $pr) {
                PermissionRole::firstOrCreate(
                    ['permission_id' => $pr->permission_id, 'role_id' => $superAdminRole->id],
                    ['permission_type_id' => $pr->permission_type_id]
                );
            }
        }

        // Grant accountant: accounts module permissions
        $accountantRole = Role::where('name', 'accountant')->where('company_id', $companyId)->first();
        if ($accountantRole) {
            $this->grantPermissionsByNames($accountantRole, [
                'view_financial_year',
                'add_financial_year',
                'view_chart_of_account',
                'add_chart_of_account',
                'view_journal_voucher',
                'add_journal_voucher',
                'edit_journal_voucher',
                'receive_payment',
                'make_payment',
                'view_trial_balance',
                'view_ledger_report',
                'view_cash_receipt',
                'view_travel_payment',
                'view_exchange_rate',
                'add_exchange_rate',
                'view_account_opening',
                'add_account_opening',
            ]);
        }

        // Grant visa-officer: booking view + visa pipeline
        $visaRole = Role::where('name', 'visa-officer')->where('company_id', $companyId)->first();
        if ($visaRole) {
            $this->grantPermissionsByNames($visaRole, [
                'view_booking',
                'view_visa_company',
            ]);
        }

        // Grant ops-staff: booking view + room allocation implied by booking view
        $opsRole = Role::where('name', 'ops-staff')->where('company_id', $companyId)->first();
        if ($opsRole) {
            $this->grantPermissionsByNames($opsRole, [
                'view_booking',
                'edit_booking',
                'manage_passport_delivery',
                'view_hotel',
                'view_package',
            ]);
        }

        // Grant ticketing-staff: ticketing module
        $ticketingRole = Role::where('name', 'ticketing-staff')->where('company_id', $companyId)->first();
        if ($ticketingRole) {
            $this->grantPermissionsByNames($ticketingRole, [
                'add_ticket_invoice',
                'view_ticket_invoice',
                'edit_ticket_invoice',
                'delete_ticket_invoice',
                'add_ticket_refund',
                'view_ticket_report',
            ]);
        }
    }

    /**
     * Helper: grant a list of permission names to a role (ALL type).
     */
    private function grantPermissionsByNames(Role $role, array $permNames): void
    {
        foreach ($permNames as $permName) {
            $permission = Permission::where('name', $permName)->first();
            if ($permission) {
                PermissionRole::firstOrCreate(
                    ['permission_id' => $permission->id, 'role_id' => $role->id],
                    ['permission_type_id' => PermissionType::ALL]
                );
            }
        }
    }

    // ────────────────────────────────────────────────────────────────────────
    // 14. Users (10 demo accounts)
    // ────────────────────────────────────────────────────────────────────────

    private function seedUsers(): array
    {
        $this->command->info('[14/14] Seeding Demo Users...');

        $companyId = $this->company->id;

        // Resolve base roles
        $adminRole    = Role::where('name', 'admin')->where('company_id', $companyId)->first();
        $clientRole   = Role::where('name', 'client')->where('company_id', $companyId)->first();
        $employeeRole = Role::where('name', 'employee')->where('company_id', $companyId)->first();

        // Resolve custom roles (names stored with hyphens due to str_slug mutator)
        $superAdminRole  = Role::where('name', 'super-admin')->where('company_id', $companyId)->first();
        $accountantRole  = Role::where('name', 'accountant')->where('company_id', $companyId)->first();
        $visaRole        = Role::where('name', 'visa-officer')->where('company_id', $companyId)->first();
        $opsRole         = Role::where('name', 'ops-staff')->where('company_id', $companyId)->first();
        $ticketingRole   = Role::where('name', 'ticketing-staff')->where('company_id', $companyId)->first();

        // Customer types
        $typeAgent    = CustomerType::where('name', 'B2B Agent')->where('company_id', $companyId)->first();
        $typeCustomer = CustomerType::where('name', 'B2C Customer')->where('company_id', $companyId)->first();
        $typeSubAgent = CustomerType::where('name', 'Sub-Agent')->where('company_id', $companyId)->first();

        $users = [];

        // ── Super Admin ──────────────────────────────────────────────────────
        $superAdmin = $this->createEmployeeUser(
            email:       'superadmin@zapflow.test',
            name:        'Zafar Khan (Super Admin)',
            gender:      'male',
            roles:       [$superAdminRole, $adminRole],
            companyId:   $companyId,
            employeeRole: $employeeRole
        );
        $users['superAdmin'] = $superAdmin;

        // ── Admin ────────────────────────────────────────────────────────────
        $admin = $this->createEmployeeUser(
            email:       'admin@zapflow.test',
            name:        'Fatima Malik (Admin)',
            gender:      'female',
            roles:       [$adminRole],
            companyId:   $companyId,
            employeeRole: $employeeRole
        );
        $users['admin'] = $admin;

        // ── Accountant ───────────────────────────────────────────────────────
        $accountant = $this->createEmployeeUser(
            email:       'accountant@zapflow.test',
            name:        'Bilal Ahmed (Accountant)',
            gender:      'male',
            roles:       [$accountantRole, $employeeRole],
            companyId:   $companyId,
            employeeRole: $employeeRole
        );
        $users['accountant'] = $accountant;

        // ── Visa Officer ─────────────────────────────────────────────────────
        $visaOfficer = $this->createEmployeeUser(
            email:       'visaofficer@zapflow.test',
            name:        'Ayesha Siddiqui (Visa Officer)',
            gender:      'female',
            roles:       [$visaRole, $employeeRole],
            companyId:   $companyId,
            employeeRole: $employeeRole
        );
        $users['visaOfficer'] = $visaOfficer;

        // ── Ops Staff ────────────────────────────────────────────────────────
        $opsStaff = $this->createEmployeeUser(
            email:       'opsstaff@zapflow.test',
            name:        'Usman Tariq (Ops Staff)',
            gender:      'male',
            roles:       [$opsRole, $employeeRole],
            companyId:   $companyId,
            employeeRole: $employeeRole
        );
        $users['opsStaff'] = $opsStaff;

        // ── Ticketing Staff ──────────────────────────────────────────────────
        $ticketing = $this->createEmployeeUser(
            email:       'ticketing@zapflow.test',
            name:        'Nadia Rauf (Ticketing)',
            gender:      'female',
            roles:       [$ticketingRole, $employeeRole],
            companyId:   $companyId,
            employeeRole: $employeeRole
        );
        $users['ticketing'] = $ticketing;

        // ── Agent 1 (Tier-1 B2B) ────────────────────────────────────────────
        $agent1 = $this->createClientUser(
            email:          'agent1@zapflow.test',
            name:           'Al-Noor Travel Agency (Agent-1)',
            gender:         'male',
            clientRole:     $clientRole,
            companyId:      $companyId,
            customerType:   $typeAgent,
            parentCustomerId: null
        );
        // Create Umrah Account + Ticket Account for Agent1
        $agent1UmrahAccount  = $this->createAgentAccount($agent1, 'Umrah', '1110', $companyId);
        $agent1TicketAccount = $this->createAgentAccount($agent1, 'Ticket', '1111', $companyId);
        // Link accounts to client_details
        DB::table('client_details')
            ->where('user_id', $agent1->id)
            ->update([
                'umrah_account_id'  => $agent1UmrahAccount->id,
                'ticket_account_id' => $agent1TicketAccount->id,
            ]);
        $users['agent1']              = $agent1;
        $users['agent1UmrahAccount']  = $agent1UmrahAccount;
        $users['agent1TicketAccount'] = $agent1TicketAccount;

        // ── Agent 2 (Tier-1 B2B) ────────────────────────────────────────────
        $agent2 = $this->createClientUser(
            email:          'agent2@zapflow.test',
            name:           'Barakah Hajj Group (Agent-2)',
            gender:         'male',
            clientRole:     $clientRole,
            companyId:      $companyId,
            customerType:   $typeAgent,
            parentCustomerId: null
        );
        $agent2UmrahAccount  = $this->createAgentAccount($agent2, 'Umrah', '1112', $companyId);
        $agent2TicketAccount = $this->createAgentAccount($agent2, 'Ticket', '1113', $companyId);
        DB::table('client_details')
            ->where('user_id', $agent2->id)
            ->update([
                'umrah_account_id'  => $agent2UmrahAccount->id,
                'ticket_account_id' => $agent2TicketAccount->id,
            ]);
        $users['agent2']              = $agent2;
        $users['agent2UmrahAccount']  = $agent2UmrahAccount;
        $users['agent2TicketAccount'] = $agent2TicketAccount;

        // ── Sub-Agent (Tier-2, under agent1) ────────────────────────────────
        $subAgent = $this->createClientUser(
            email:          'subagent1@zapflow.test',
            name:           'Rahmat Sub-Agency (Sub-Agent of Agent1)',
            gender:         'male',
            clientRole:     $clientRole,
            companyId:      $companyId,
            customerType:   $typeSubAgent,
            parentCustomerId: $agent1->id
        );
        $users['subAgent'] = $subAgent;

        // ── B2C Customer ─────────────────────────────────────────────────────
        $customer = $this->createClientUser(
            email:          'customer@zapflow.test',
            name:           'Hassan Iqbal (B2C Customer)',
            gender:         'male',
            clientRole:     $clientRole,
            companyId:      $companyId,
            customerType:   $typeCustomer,
            parentCustomerId: null
        );
        $users['customer'] = $customer;

        return $users;
    }

    /**
     * Create an employee-type demo user (for internal staff roles).
     */
    private function createEmployeeUser(
        string $email,
        string $name,
        string $gender,
        array  $roles,
        int    $companyId,
        ?Role  $employeeRole
    ): User {
        $existing = User::where('email', $email)->first();
        if ($existing) {
            return $existing;
        }

        $user              = new User();
        $user->name        = $name;
        $user->email       = $email;
        $user->password    = $this->demoPassword;
        $user->company_id  = $companyId;
        $user->gender      = $gender;
        $user->save();

        // Employee details
        $deptId  = DB::table('teams')->where('company_id', $companyId)->value('id') ?? 1;
        $desigId = DB::table('designations')->where('company_id', $companyId)->value('id') ?? 1;

        $employee                = new EmployeeDetails();
        $employee->user_id       = $user->id;
        $employee->company_id    = $companyId;
        $employee->employee_id   = 'DEMO-' . strtoupper(substr(md5($email), 0, 6));
        $employee->about_me      = 'Demo account for testing.';
        $employee->hourly_rate   = 0;
        $employee->department_id = $deptId;
        $employee->designation_id = $desigId;
        $employee->joining_date  = now()->subYear()->toDateTimeString();
        $employee->marital_status = MaritalStatus::Single;
        $employee->save();

        // Assign roles (deduplicate)
        $roleIds = array_unique(array_filter(array_map(fn($r) => $r?->id, $roles)));
        foreach ($roleIds as $roleId) {
            DB::table('role_user')->insertOrIgnore([
                'user_id' => $user->id,
                'role_id' => $roleId,
            ]);
        }

        // Universal search
        UniversalSearch::create([
            'searchable_id' => $user->id,
            'company_id'    => $companyId,
            'title'         => $user->name,
            'route_name'    => 'employees.show',
            'module_type'   => 'employee',
        ]);

        return $user;
    }

    /**
     * Create a client-type demo user (agents, sub-agents, customers).
     */
    private function createClientUser(
        string       $email,
        string       $name,
        string       $gender,
        ?Role        $clientRole,
        int          $companyId,
        ?CustomerType $customerType,
        ?int         $parentCustomerId
    ): User {
        $existing = User::where('email', $email)->first();
        if ($existing) {
            return $existing;
        }

        $user              = new User();
        $user->name        = $name;
        $user->email       = $email;
        $user->password    = $this->demoPassword;
        $user->company_id  = $companyId;
        $user->gender      = $gender;
        $user->save();

        // Client details
        $client               = new ClientDetails();
        $client->user_id      = $user->id;
        $client->company_id   = $companyId;
        $client->company_name = $name;
        $client->address      = 'Pakistan';
        $client->website      = '';
        $client->save();

        // Apply customer_type_id and parent_customer_id via raw DB
        // (ClientDetails->fillable doesn't include the travel-specific columns)
        $updates = [];
        if ($customerType) {
            $updates['customer_type_id'] = $customerType->id;
        }
        if ($parentCustomerId) {
            $updates['parent_customer_id'] = $parentCustomerId;
        }
        if ($updates) {
            DB::table('client_details')->where('user_id', $user->id)->update($updates);
        }

        // Assign client role
        if ($clientRole) {
            DB::table('role_user')->insertOrIgnore([
                'user_id' => $user->id,
                'role_id' => $clientRole->id,
            ]);
        }

        // Universal search
        UniversalSearch::create([
            'searchable_id' => $user->id,
            'company_id'    => $companyId,
            'title'         => $user->name,
            'route_name'    => 'clients.show',
            'module_type'   => 'client',
        ]);

        return $user;
    }

    /**
     * Create a dedicated Chart of Account for an agent's ledger.
     */
    private function createAgentAccount(User $agent, string $type, string $code, int $companyId): ChartOfAccount
    {
        return ChartOfAccount::firstOrCreate(
            ['code' => $code, 'company_id' => $companyId],
            [
                'name'            => "{$agent->name} — {$type} Account",
                'type'            => 'asset',
                'is_bank_account' => false,
                'level'           => 2,
                'parent_id'       => $this->accounts['ar']->id,
                'added_by'        => $this->systemAdmin->id,
            ]
        );
    }

    // ────────────────────────────────────────────────────────────────────────
    // 15. Booking Group — Agent 1 (Golden Path data)
    // ────────────────────────────────────────────────────────────────────────

    private function seedAgent1BookingGroup(array $users): void
    {
        $this->command->info('[+] Seeding Agent-1 Booking Group (Golden Path)...');

        $companyId = $this->company->id;
        $by        = $this->systemAdmin->id;

        // BookingGroup
        $booking = BookingGroup::firstOrCreate(
            ['group_name' => 'Demo Umrah Group Alpha', 'company_id' => $companyId],
            [
                'group_no'       => 'BG-2026-001',
                'customer_id'    => $users['agent1']->id,
                'package_id'     => $this->accounts['package_id'],
                'departure_date' => now()->addMonths(2)->toDateString(),
                'return_date'    => now()->addMonths(2)->addDays(21)->toDateString(),
                'notes'          => 'Demo booking created by DemoDataSeeder for manual testing.',
                'added_by'       => $by,
            ]
        );

        // Booking Charge — posts against agent's Umrah Account
        BookingCharge::firstOrCreate(
            ['booking_group_id' => $booking->id, 'charge_type' => 'package_fee'],
            [
                'amount'      => 280000.00,
                'account_id'  => $users['agent1UmrahAccount']->id,
                'description' => 'Gold Umrah Package × 4 passengers @ PKR 70,000 each',
                'added_by'    => $by,
            ]
        );

        // Passengers (4, mixed gender, mixed visa stages)
        $p1 = $this->createPassenger($booking->id, 'Ahmad',    'Raza',    '1990-03-15', 'Male',   'AA1234567', 'draft',            null,                    $by);
        $p2 = $this->createPassenger($booking->id, 'Kiran',    'Bano',    '1985-07-22', 'Female', 'BB9876543', 'sent_to_embassy',  null,                    $by);
        $p3 = $this->createPassenger($booking->id, 'Tariq',    'Mehmood', '1978-11-08', 'Male',   'CC5544332', 'mofa_received',    'MOFA-2026-TM-004481',  $by);
        $p4 = $this->createPassenger($booking->id, 'Sana',     'Fatima',  '1995-04-30', 'Female', 'DD1122334', 'issued',           null,                    $by);

        // Visa Logs (for passengers 2, 3, 4 to simulate history)
        VisaLog::create([
            'company_id'   => $companyId,
            'passenger_id' => $p2->id,
            'from_status'  => 'draft',
            'to_status'    => 'sent_to_embassy',
            'changed_by'   => $users['visaOfficer']->id,
            'remarks'      => 'Submitted to Saudi Embassy.',
        ]);

        VisaLog::create([
            'company_id'   => $companyId,
            'passenger_id' => $p3->id,
            'from_status'  => 'draft',
            'to_status'    => 'sent_to_embassy',
            'changed_by'   => $users['visaOfficer']->id,
            'remarks'      => 'Submitted to Saudi Embassy.',
        ]);
        VisaLog::create([
            'company_id'   => $companyId,
            'passenger_id' => $p3->id,
            'from_status'  => 'sent_to_embassy',
            'to_status'    => 'mofa_received',
            'changed_by'   => $users['visaOfficer']->id,
            'remarks'      => 'MoFA clearance received.',
        ]);

        VisaLog::create([
            'company_id'   => $companyId,
            'passenger_id' => $p4->id,
            'from_status'  => 'draft',
            'to_status'    => 'sent_to_embassy',
            'changed_by'   => $users['visaOfficer']->id,
            'remarks'      => 'Submitted to Saudi Embassy.',
        ]);
        VisaLog::create([
            'company_id'   => $companyId,
            'passenger_id' => $p4->id,
            'from_status'  => 'sent_to_embassy',
            'to_status'    => 'mofa_received',
            'changed_by'   => $users['visaOfficer']->id,
            'remarks'      => 'MoFA clearance received.',
        ]);
        VisaLog::create([
            'company_id'   => $companyId,
            'passenger_id' => $p4->id,
            'from_status'  => 'mofa_received',
            'to_status'    => 'issued',
            'changed_by'   => $users['visaOfficer']->id,
            'remarks'      => 'Visa stamped and collected.',
        ]);

        // Room Allocations (Ops Staff test)
        $maleRoom   = HotelRoom::where('room_number', 'MK-101')->first();
        $femaleRoom = HotelRoom::where('room_number', 'MK-102')->first();

        if ($maleRoom) {
            RoomAllocation::firstOrCreate(
                ['hotel_room_id' => $maleRoom->id, 'passenger_id' => $p1->id],
                [
                    'booking_group_id' => $booking->id,
                    'check_in'         => now()->addMonths(2)->toDateString(),
                    'check_out'        => now()->addMonths(2)->addDays(14)->toDateString(),
                    'status'           => 'reserved',
                    'allocated_by'     => $users['opsStaff']->id,
                ]
            );
        }

        if ($femaleRoom) {
            RoomAllocation::firstOrCreate(
                ['hotel_room_id' => $femaleRoom->id, 'passenger_id' => $p2->id],
                [
                    'booking_group_id' => $booking->id,
                    'check_in'         => now()->addMonths(2)->toDateString(),
                    'check_out'        => now()->addMonths(2)->addDays(14)->toDateString(),
                    'status'           => 'reserved',
                    'allocated_by'     => $users['opsStaff']->id,
                ]
            );
        }

        // Voucher — Draft (tester can lock it)
        Voucher::firstOrCreate(
            ['booking_group_id' => $booking->id, 'voucher_number' => 'VCH-2026-0001'],
            [
                'company_id' => $companyId,
                'type'       => 'full',
                'status'     => 'draft',
                'added_by'   => $by,
            ]
        );

        // Insurance Sale — Passenger 1
        InsuranceSale::firstOrCreate(
            ['passenger_id' => $p1->id, 'policy_id' => $this->accounts['travel_policy_id']],
            [
                'company_id'         => $companyId,
                'booking_group_id'   => $booking->id,
                'amount'             => 1500.00,
                'currency_code'      => 'PKR',
                'certificate_number' => 'EFU-TRV-2026-001',
                'status'             => 'active',
                'added_by'           => $by,
            ]
        );

        // Travel Payment — Receive (from Agent1)
        $jvFy  = $this->accounts['financial_year_id'];
        $jvNum = 'JV-DEMO-001';

        $jv1 = JournalVoucher::firstOrCreate(
            ['voucher_number' => $jvNum, 'company_id' => $companyId],
            [
                'financial_year_id' => $jvFy,
                'date'              => now()->toDateString(),
                'created_by'        => $users['accountant']->id,
                'narration'         => 'Demo: Agent1 payment received for BG-2026-001',
                'is_balanced'       => true,
                'added_by'          => $by,
            ]
        );

        // JV Lines: Dr Bank / Cr Agent AR
        JournalVoucherLine::firstOrCreate(
            ['journal_voucher_id' => $jv1->id, 'account_id' => $this->accounts['bank']->id],
            ['debit' => 150000.00, 'credit' => 0, 'description' => 'Cash received from agent1']
        );
        JournalVoucherLine::firstOrCreate(
            ['journal_voucher_id' => $jv1->id, 'account_id' => $users['agent1UmrahAccount']->id],
            ['debit' => 0, 'credit' => 150000.00, 'description' => 'Agent1 Umrah Account credit']
        );

        // TravelPayment record — receive
        DB::table('travel_payments')->insertOrIgnore([
            'company_id'           => $companyId,
            'payment_direction'    => 'receive',
            'party_user_id'        => $users['agent1']->id,
            'debit_account_id'     => $this->accounts['bank']->id,
            'credit_account_id'    => $users['agent1UmrahAccount']->id,
            'amount'               => 150000.00,
            'currency_code'        => 'PKR',
            'exchange_rate'        => 1.000000,
            'amount_base_currency' => 150000.00,
            'payment_date'         => now()->toDateString(),
            'payment_method'       => 'bank_transfer',
            'reference_no'         => 'TXN-' . now()->format('Ymd') . '-001',
            'narration'            => 'Partial payment from Al-Noor Travel Agency',
            'booking_group_id'     => $booking->id,
            'journal_voucher_id'   => $jv1->id,
            'status'               => 'posted',
            'added_by'             => $users['accountant']->id,
            'created_at'           => now(),
            'updated_at'           => now(),
        ]);

        // Travel Payment — Make (to Hotel vendor)
        $jvNum2 = 'JV-DEMO-002';
        $jv2 = JournalVoucher::firstOrCreate(
            ['voucher_number' => $jvNum2, 'company_id' => $companyId],
            [
                'financial_year_id' => $jvFy,
                'date'              => now()->toDateString(),
                'created_by'        => $users['accountant']->id,
                'narration'         => 'Demo: Hotel advance payment for BG-2026-001',
                'is_balanced'       => true,
                'added_by'          => $by,
            ]
        );

        // JV Lines: Dr Hotel Expense / Cr Bank
        JournalVoucherLine::firstOrCreate(
            ['journal_voucher_id' => $jv2->id, 'account_id' => $this->accounts['hotel_exp']->id],
            ['debit' => 80000.00, 'credit' => 0, 'description' => 'Hotel advance — Zam Zam Tower']
        );
        JournalVoucherLine::firstOrCreate(
            ['journal_voucher_id' => $jv2->id, 'account_id' => $this->accounts['bank']->id],
            ['debit' => 0, 'credit' => 80000.00, 'description' => 'Bank payment to hotel']
        );

        // TravelPayment record — make
        DB::table('travel_payments')->insertOrIgnore([
            'company_id'           => $companyId,
            'payment_direction'    => 'make',
            'party_user_id'        => null,
            'debit_account_id'     => $this->accounts['hotel_exp']->id,
            'credit_account_id'    => $this->accounts['bank']->id,
            'amount'               => 80000.00,
            'currency_code'        => 'PKR',
            'exchange_rate'        => 1.000000,
            'amount_base_currency' => 80000.00,
            'payment_date'         => now()->toDateString(),
            'payment_method'       => 'bank_transfer',
            'reference_no'         => 'TXN-' . now()->format('Ymd') . '-002',
            'narration'            => 'Hotel advance to Zam Zam Tower Hotel, Makkah',
            'booking_group_id'     => $booking->id,
            'journal_voucher_id'   => $jv2->id,
            'status'               => 'posted',
            'added_by'             => $users['accountant']->id,
            'created_at'           => now(),
            'updated_at'           => now(),
        ]);

        // Revenue recognition JV: Dr Agent1 AR / Cr Umrah Revenue
        $jvNum3 = 'JV-DEMO-003';
        $jv3 = JournalVoucher::firstOrCreate(
            ['voucher_number' => $jvNum3, 'company_id' => $companyId],
            [
                'financial_year_id' => $jvFy,
                'date'              => now()->toDateString(),
                'created_by'        => $users['accountant']->id,
                'narration'         => 'Demo: Revenue recognition for BG-2026-001',
                'is_balanced'       => true,
                'added_by'          => $by,
            ]
        );

        JournalVoucherLine::firstOrCreate(
            ['journal_voucher_id' => $jv3->id, 'account_id' => $users['agent1UmrahAccount']->id],
            ['debit' => 280000.00, 'credit' => 0, 'description' => 'Agent1 receivable — package fee']
        );
        JournalVoucherLine::firstOrCreate(
            ['journal_voucher_id' => $jv3->id, 'account_id' => $this->accounts['umrah_rev']->id],
            ['debit' => 0, 'credit' => 280000.00, 'description' => 'Umrah revenue recognised']
        );

        $this->accounts['booking_group_id'] = $booking->id;
    }

    // ────────────────────────────────────────────────────────────────────────
    // 16. Booking Group — Agent 2 (isolation test)
    // ────────────────────────────────────────────────────────────────────────

    private function seedAgent2BookingGroup(array $users): void
    {
        $this->command->info('[+] Seeding Agent-2 Booking Group (isolation test)...');

        $companyId = $this->company->id;
        $by        = $this->systemAdmin->id;

        $booking2 = BookingGroup::firstOrCreate(
            ['group_name' => 'Demo Umrah Group Beta', 'company_id' => $companyId],
            [
                'group_no'       => 'BG-2026-002',
                'customer_id'    => $users['agent2']->id,
                'package_id'     => $this->accounts['package_id'],
                'departure_date' => now()->addMonths(3)->toDateString(),
                'return_date'    => now()->addMonths(3)->addDays(21)->toDateString(),
                'notes'          => 'Agent2 isolation test booking.',
                'added_by'       => $by,
            ]
        );

        $this->createPassenger($booking2->id, 'Shahid',  'Ullah',  '1982-06-10', 'Male',   'EE7788990', 'draft', null, $by);
        $this->createPassenger($booking2->id, 'Raheela', 'Bibi',   '1984-09-25', 'Female', 'FF3344556', 'draft', null, $by);
    }

    // ────────────────────────────────────────────────────────────────────────
    // Helper: Create a passenger record
    // ────────────────────────────────────────────────────────────────────────

    private function createPassenger(
        int     $bookingGroupId,
        string  $firstName,
        string  $familyName,
        string  $birthDate,
        string  $gender,
        string  $passportNo,
        string  $visaStatus,
        ?string $mofaRef,
        int     $addedBy
    ): Passenger {
        $existing = Passenger::where('passport_no', $passportNo)
            ->where('booking_group_id', $bookingGroupId)
            ->first();

        if ($existing) {
            return $existing;
        }

        $p = new Passenger();
        $p->booking_group_id       = $bookingGroupId;
        $p->passport_no            = $passportNo;
        $p->first_name             = $firstName;
        $p->family_name            = $familyName;
        $p->birth_date             = $birthDate;
        $p->gender                 = $gender;
        $p->visa_pipeline_status   = $visaStatus;
        $p->visa_mofa_ref          = $mofaRef;
        $p->mofa_status            = $mofaRef ? '1' : '0';
        $p->added_by               = $addedBy;
        $p->save();

        return $p;
    }

    // ────────────────────────────────────────────────────────────────────────
    // Final: Print summary table
    // ────────────────────────────────────────────────────────────────────────

    private function printSummary(array $users): void
    {
        $this->command->info('');
        $this->command->info('╔══════════════════════════════════════════════════════════════════════════╗');
        $this->command->info('║                  ZapFlow Demo Data — Seed Complete                      ║');
        $this->command->info('╠══════════════════════════════════════════════════════════════════════════╣');
        $this->command->info('║  ALL ACCOUNTS USE PASSWORD: Password123!                                ║');
        $this->command->info('║  (Testing only — never use this password in production)                 ║');
        $this->command->info('╠══════════════════╦═══════════════════════════════════╦══════════════════╣');
        $this->command->info('║ Role             ║ Email                             ║ User ID          ║');
        $this->command->info('╠══════════════════╬═══════════════════════════════════╬══════════════════╣');
        $this->printRow('Super Admin',    'superadmin@zapflow.test',  $users['superAdmin']->id);
        $this->printRow('Admin',          'admin@zapflow.test',       $users['admin']->id);
        $this->printRow('Accountant',     'accountant@zapflow.test',  $users['accountant']->id);
        $this->printRow('Visa Officer',   'visaofficer@zapflow.test', $users['visaOfficer']->id);
        $this->printRow('Ops Staff',      'opsstaff@zapflow.test',    $users['opsStaff']->id);
        $this->printRow('Ticketing',      'ticketing@zapflow.test',   $users['ticketing']->id);
        $this->printRow('Agent-1 (B2B)',  'agent1@zapflow.test',      $users['agent1']->id);
        $this->printRow('Agent-2 (B2B)',  'agent2@zapflow.test',      $users['agent2']->id);
        $this->printRow('Sub-Agent',      'subagent1@zapflow.test',   $users['subAgent']->id);
        $this->printRow('Customer (B2C)', 'customer@zapflow.test',    $users['customer']->id);
        $this->command->info('╠══════════════════╩═══════════════════════════════════╩══════════════════╣');
        $this->command->info('║ Booking Group (Agent1) : Demo Umrah Group Alpha  (BG-2026-001)          ║');
        $this->command->info('║ Booking Group (Agent2) : Demo Umrah Group Beta   (BG-2026-002)          ║');
        $this->command->info('║ Draft Voucher          : VCH-2026-0001 (lock this in Section B step 6)  ║');
        $this->command->info('║ Journal Vouchers       : JV-DEMO-001, JV-DEMO-002, JV-DEMO-003          ║');
        $this->command->info('╚══════════════════════════════════════════════════════════════════════════╝');
    }

    private function printRow(string $role, string $email, int $id): void
    {
        $this->command->info(sprintf(
            '║ %-16s ║ %-33s ║ %-16d ║',
            $role,
            $email,
            $id
        ));
    }
}
