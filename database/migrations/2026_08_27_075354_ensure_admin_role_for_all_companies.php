<?php

use App\Models\Permission;
use App\Models\PermissionRole;
use App\Models\PermissionType;
use App\Models\Role;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * This migration hardens the Gate::before('admin') bypass.
 *
 * Normally, the 'admin' role is created by App\Observers\CompanyObserver::roles()
 * which fires on company creation. However, if a company already exists in the
 * database (e.g., restored from a backup, imported from a non-standard install,
 * or created without going through the observer), the 'admin' role may be
 * missing — silently disabling all permission checks for any user who would
 * otherwise have held that role.
 *
 * This migration:
 *   1. Iterates every existing company.
 *   2. Ensures an 'admin' role exists for that company.
 *   3. Attaches ALL permissions to that role with type ALL.
 *
 * It is idempotent: if the role already exists, it is left in place; the
 * permission grants use firstOrCreate.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('companies') || !Schema::hasTable('roles') || !Schema::hasTable('permissions')) {
            return;
        }

        $companies = DB::table('companies')->get();
        $allPermissions = Permission::all();

        foreach ($companies as $company) {
            $adminRole = Role::where('name', 'admin')
                ->where('company_id', $company->id)
                ->first();

            if (!$adminRole) {
                $adminRole = new Role();
                $adminRole->name         = 'admin';
                $adminRole->company_id   = $company->id;
                $adminRole->display_name = 'App Administrator';
                $adminRole->description  = 'Admin is allowed to manage everything of the app.';
                $adminRole->save();
            }

            foreach ($allPermissions as $permission) {
                PermissionRole::firstOrCreate(
                    [
                        'permission_id' => $permission->id,
                        'role_id'       => $adminRole->id,
                    ],
                    [
                        'permission_type_id' => PermissionType::ALL,
                    ]
                );
            }
        }
    }

    public function down(): void
    {
        // No-op: we don't delete roles on rollback because that would orphan
        // user-role assignments created after this migration ran.
    }
};
