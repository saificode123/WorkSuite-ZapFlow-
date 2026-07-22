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
        Schema::table('journal_voucher_lines', function (Blueprint $table) {
            $table->integer('booking_group_id')->unsigned()->nullable()->after('description');
            $table->foreign('booking_group_id')->references('id')->on('booking_groups')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('journal_voucher_lines', function (Blueprint $table) {
            //
        });
    }
};
