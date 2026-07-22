<?php

use App\Models\Module;
use App\Models\ModuleSetting;
use App\Models\Permission;
use App\Models\PermissionRole;
use App\Models\PermissionType;
use App\Models\Role;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * This migration fixes the silent failure of 2026_01_01_000018_add_travel_agency_module_settings.
 * That migration used Module::firstOrCreate(['module_name' => ..., 'company_id' => null])
 * but the modules table has NO company_id column, so every firstOrCreate threw an exception
 * and was swallowed, leaving modules & permissions un-created.
 * The migration was still recorded as "Ran" in the migrations table, causing the
 * "Undefined array key view_hotel" 500 error in menu.blade.php.
 */
return new class extends Migration
{
    const NEW_MODULES = [
        [
            'module_name' => 'accounts',
            'permissions' => [
                ['name' => 'add_financial_year',    'allowed_permissions' => Permission::ALL_NONE],
                ['name' => 'view_financial_year',   'allowed_permissions' => Permission::ALL_NONE],
                ['name' => 'edit_financial_year',   'allowed_permissions' => Permission::ALL_NONE],
                ['name' => 'delete_financial_year', 'allowed_permissions' => Permission::ALL_NONE],
                ['name' => 'add_chart_of_account',  'allowed_permissions' => Permission::ALL_NONE],
                ['name' => 'view_chart_of_account', 'allowed_permissions' => Permission::ALL_NONE],
                ['name' => 'edit_chart_of_account', 'allowed_permissions' => Permission::ALL_NONE],
                ['name' => 'delete_chart_of_account','allowed_permissions'=> Permission::ALL_NONE],
                ['name' => 'add_journal_voucher',   'allowed_permissions' => Permission::ALL_NONE],
                ['name' => 'view_journal_voucher',  'allowed_permissions' => Permission::ALL_NONE],
                ['name' => 'edit_journal_voucher',  'allowed_permissions' => Permission::ALL_NONE],
                ['name' => 'delete_journal_voucher','allowed_permissions' => Permission::ALL_NONE],
                ['name' => 'add_account_opening',   'allowed_permissions' => Permission::ALL_NONE],
                ['name' => 'view_account_opening',  'allowed_permissions' => Permission::ALL_NONE],
                ['name' => 'add_exchange_rate',     'allowed_permissions' => Permission::ALL_NONE],
                ['name' => 'view_exchange_rate',    'allowed_permissions' => Permission::ALL_NONE],
                ['name' => 'edit_exchange_rate',    'allowed_permissions' => Permission::ALL_NONE],
                ['name' => 'delete_exchange_rate',  'allowed_permissions' => Permission::ALL_NONE],
                ['name' => 'receive_payment',       'allowed_permissions' => Permission::ALL_NONE],
                ['name' => 'make_payment',          'allowed_permissions' => Permission::ALL_NONE],
                ['name' => 'view_trial_balance',    'allowed_permissions' => Permission::ALL_NONE],
                ['name' => 'view_ledger_report',    'allowed_permissions' => Permission::ALL_NONE],
                ['name' => 'view_cash_receipt',     'allowed_permissions' => Permission::ALL_NONE],
                ['name' => 'view_travel_payment',   'allowed_permissions' => Permission::ALL_NONE],
            ],
        ],
        [
            'module_name' => 'umrah_setup',
            'permissions' => [
                ['name' => 'add_hotel',              'allowed_permissions' => Permission::ALL_NONE],
                ['name' => 'view_hotel',             'allowed_permissions' => Permission::ALL_ADDED_NONE],
                ['name' => 'edit_hotel',             'allowed_permissions' => Permission::ALL_ADDED_NONE],
                ['name' => 'delete_hotel',           'allowed_permissions' => Permission::ALL_ADDED_NONE],
                ['name' => 'add_package',            'allowed_permissions' => Permission::ALL_NONE],
                ['name' => 'view_package',           'allowed_permissions' => Permission::ALL_ADDED_NONE],
                ['name' => 'edit_package',           'allowed_permissions' => Permission::ALL_ADDED_NONE],
                ['name' => 'delete_package',         'allowed_permissions' => Permission::ALL_ADDED_NONE],
                ['name' => 'manage_lookup_tables',   'allowed_permissions' => Permission::ALL_NONE],
            ],
        ],
        [
            'module_name' => 'service_providers',
            'permissions' => [
                ['name' => 'add_service_provider',    'allowed_permissions' => Permission::ALL_NONE],
                ['name' => 'view_service_provider',   'allowed_permissions' => Permission::ALL_ADDED_NONE],
                ['name' => 'edit_service_provider',   'allowed_permissions' => Permission::ALL_ADDED_NONE],
                ['name' => 'delete_service_provider', 'allowed_permissions' => Permission::ALL_ADDED_NONE],
                ['name' => 'add_iata',                'allowed_permissions' => Permission::ALL_NONE],
                ['name' => 'view_iata',               'allowed_permissions' => Permission::ALL_ADDED_NONE],
                ['name' => 'edit_iata',               'allowed_permissions' => Permission::ALL_ADDED_NONE],
                ['name' => 'delete_iata',             'allowed_permissions' => Permission::ALL_ADDED_NONE],
                ['name' => 'add_visa_company',        'allowed_permissions' => Permission::ALL_NONE],
                ['name' => 'view_visa_company',       'allowed_permissions' => Permission::ALL_ADDED_NONE],
                ['name' => 'edit_visa_company',       'allowed_permissions' => Permission::ALL_ADDED_NONE],
                ['name' => 'delete_visa_company',     'allowed_permissions' => Permission::ALL_ADDED_NONE],
                ['name' => 'add_transporter',         'allowed_permissions' => Permission::ALL_NONE],
                ['name' => 'view_transporter',        'allowed_permissions' => Permission::ALL_ADDED_NONE],
                ['name' => 'edit_transporter',        'allowed_permissions' => Permission::ALL_ADDED_NONE],
                ['name' => 'delete_transporter',      'allowed_permissions' => Permission::ALL_ADDED_NONE],
                ['name' => 'add_airline',             'allowed_permissions' => Permission::ALL_NONE],
                ['name' => 'view_airline',            'allowed_permissions' => Permission::ALL_ADDED_NONE],
                ['name' => 'edit_airline',            'allowed_permissions' => Permission::ALL_ADDED_NONE],
                ['name' => 'delete_airline',          'allowed_permissions' => Permission::ALL_ADDED_NONE],
            ],
        ],
        [
            'module_name' => 'bookings',
            'permissions' => [
                ['name' => 'add_booking',              'allowed_permissions' => Permission::ALL_NONE],
                ['name' => 'view_booking',             'allowed_permissions' => Permission::ALL_4_ADDED_1_OWNED_2_BOTH_3_NONE_5],
                ['name' => 'edit_booking',             'allowed_permissions' => Permission::ALL_4_ADDED_1_OWNED_2_BOTH_3_NONE_5],
                ['name' => 'delete_booking',           'allowed_permissions' => Permission::ALL_4_ADDED_1_OWNED_2_BOTH_3_NONE_5],
                ['name' => 'import_booking',           'allowed_permissions' => Permission::ALL_NONE],
                ['name' => 'manage_passport_delivery', 'allowed_permissions' => Permission::ALL_NONE],
                ['name' => 'manage_mutamer_transfer',  'allowed_permissions' => Permission::ALL_NONE],
            ],
        ],
        [
            'module_name' => 'vouchers',
            'permissions' => [
                ['name' => 'add_voucher',    'allowed_permissions' => Permission::ALL_NONE],
                ['name' => 'view_voucher',   'allowed_permissions' => Permission::ALL_4_ADDED_1_OWNED_2_BOTH_3_NONE_5],
                ['name' => 'edit_voucher',   'allowed_permissions' => Permission::ALL_4_ADDED_1_OWNED_2_BOTH_3_NONE_5],
                ['name' => 'delete_voucher', 'allowed_permissions' => Permission::ALL_4_ADDED_1_OWNED_2_BOTH_3_NONE_5],
                ['name' => 'issue_voucher',  'allowed_permissions' => Permission::ALL_NONE],
            ],
        ],
        [
            'module_name' => 'ticketing',
            'permissions' => [
                ['name' => 'add_ticket_invoice',    'allowed_permissions' => Permission::ALL_NONE],
                ['name' => 'view_ticket_invoice',   'allowed_permissions' => Permission::ALL_4_ADDED_1_OWNED_2_BOTH_3_NONE_5],
                ['name' => 'edit_ticket_invoice',   'allowed_permissions' => Permission::ALL_4_ADDED_1_OWNED_2_BOTH_3_NONE_5],
                ['name' => 'delete_ticket_invoice', 'allowed_permissions' => Permission::ALL_4_ADDED_1_OWNED_2_BOTH_3_NONE_5],
                ['name' => 'add_ticket_refund',     'allowed_permissions' => Permission::ALL_NONE],
                ['name' => 'view_ticket_report',    'allowed_permissions' => Permission::ALL_NONE],
            ],
        ],
        [
            'module_name' => 'customer_types',
            'permissions' => [
                ['name' => 'add_customer_type',    'allowed_permissions' => Permission::ALL_NONE],
                ['name' => 'view_customer_type',   'allowed_permissions' => Permission::ALL_NONE],
                ['name' => 'edit_customer_type',   'allowed_permissions' => Permission::ALL_NONE],
                ['name' => 'delete_customer_type', 'allowed_permissions' => Permission::ALL_NONE],
            ],
        ],
    ];

    public function up(): void
    {
        $companies = DB::table('companies')->get();

        foreach (self::NEW_MODULES as $moduleDef) {
            $moduleName = $moduleDef['module_name'];

            // Create the global module record (no company_id on this table)
            $module = Module::firstOrCreate(
                ['module_name' => $moduleName],
                ['description' => $moduleName]
            );

            // Create permissions (skip if already exist)
            foreach ($moduleDef['permissions'] as $permDef) {
                Permission::firstOrCreate(
                    ['name' => $permDef['name'], 'module_id' => $module->id],
                    [
                        'display_name'        => ucwords(str_replace('_', ' ', $permDef['name'])),
                        'allowed_permissions' => $permDef['allowed_permissions'],
                        'is_custom'           => 0,
                    ]
                );
            }

            // Create module_settings per company (this table DOES have company_id)
            foreach ($companies as $company) {
                foreach (['admin', 'employee', 'client'] as $roleType) {
                    ModuleSetting::firstOrCreate(
                        ['module_name' => $moduleName, 'type' => $roleType, 'company_id' => $company->id],
                        ['status' => 'active']
                    );
                }

                // Assign all permissions to the company's admin role
                $adminRole = Role::where('name', 'admin')
                    ->where('company_id', $company->id)
                    ->first();

                if ($adminRole) {
                    $modulePermissions = Permission::where('module_id', $module->id)->get();

                    foreach ($modulePermissions as $permission) {
                        PermissionRole::firstOrCreate(
                            ['permission_id' => $permission->id, 'role_id' => $adminRole->id],
                            ['permission_type_id' => PermissionType::ALL]
                        );
                    }
                }
            }
        }
    }

    public function down(): void
    {
        foreach (self::NEW_MODULES as $moduleDef) {
            $module = Module::where('module_name', $moduleDef['module_name'])->first();
            if ($module) {
                Permission::where('module_id', $module->id)->delete();
                ModuleSetting::where('module_name', $moduleDef['module_name'])->delete();
                $module->delete();
            }
        }
    }
};
