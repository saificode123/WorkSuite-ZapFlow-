<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('vouchers', function (Blueprint $table) {
            if (!Schema::hasColumn('vouchers', 'locked_at')) {
                $table->timestamp('locked_at')->nullable()->after('status');
            }
            if (!Schema::hasColumn('vouchers', 'qr_payload')) {
                $table->text('qr_payload')->nullable()->after('locked_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vouchers', function (Blueprint $table) {
            $table->dropColumn(['locked_at', 'qr_payload']);
        });
    }
};
