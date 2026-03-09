<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->string('xero_invoice_id')->nullable()->after('invoice_number');
            $table->boolean('auto_sync_xero')->default(true)->after('xero_invoice_id');
        });
    }

    public function down()
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['xero_invoice_id', 'auto_sync_xero']);
        });
    }
};
