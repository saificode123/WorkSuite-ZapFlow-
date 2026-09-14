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
        Schema::table('passengers', function (Blueprint $table) {
            // Add FK so mahram references are referentially valid.
            // Use nullOnDelete so removing a Mahram's own record does not
            // destroy the linked passenger; instead the link clears.
            $table->foreign('mahram_passenger_id')
                ->references('id')->on('passengers')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('passengers', function (Blueprint $table) {
            $table->dropForeign(['mahram_passenger_id']);
        });
    }
};
