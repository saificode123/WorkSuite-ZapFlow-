<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::whenTableDoesntHaveColumn('discounts', 'name', function (Blueprint $table) {
            $table->string('name')->nullable()->after('company_id');
        });

        Schema::whenTableDoesntHaveColumn('discounts', 'is_active', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->after('value');
        });
    }

    public function down(): void
    {
        Schema::whenTableDoesntHaveColumn('discounts', 'name', function (Blueprint $table) {
            $table->dropColumn('name');
        });

        Schema::whenTableDoesntHaveColumn('discounts', 'is_active', function (Blueprint $table) {
            $table->dropColumn('is_active');
        });
    }
};
