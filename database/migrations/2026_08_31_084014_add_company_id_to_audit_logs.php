<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds company_id to audit_logs.
 *
 * All three places that write to audit_logs (PassengerObserver, VoucherObserver,
 * SensitiveFieldController) include a company_id — but the original migration
 * (2026_01_01_000017) did not include that column. Every write has been silently
 * failing with an "Unknown column" SQL error, keeping audit_log_count at 0.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            // Add after user_id so the column order is logical.
            $table->unsignedBigInteger('company_id')->nullable()->after('user_id');

            // Index on company_id is useful for filtered queries and per-company
            // audit dashboards.
            $table->index('company_id');
        });
    }

    public function down(): void
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->dropIndex(['company_id']);
            $table->dropColumn('company_id');
        });
    }
};
