<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::whenTableDoesntHaveColumn('iata_records', 'status', function (Blueprint $table) {
            $table->string('status')->default('active')->after('name');
        });
    }

    public function down(): void
    {
        Schema::whenTableDoesntHaveColumn('iata_records', 'status', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
