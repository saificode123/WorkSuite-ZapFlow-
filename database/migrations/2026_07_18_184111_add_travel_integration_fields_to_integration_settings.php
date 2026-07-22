<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('integration_settings', function (Blueprint $table) {
            $table->string('ticketing_implementation')->default('manual')->after('xero_scopes');
            $table->text('ticketing_credentials')->nullable()->after('ticketing_implementation');
            $table->string('visa_tracking_implementation')->default('manual')->after('ticketing_credentials');
            $table->text('visa_tracking_credentials')->nullable()->after('visa_tracking_implementation');
            $table->string('iata_lookup_implementation')->default('manual')->after('visa_tracking_credentials');
            $table->text('iata_lookup_credentials')->nullable()->after('iata_lookup_implementation');
            $table->string('nusuk_import_implementation')->default('manual')->after('iata_lookup_credentials');
            $table->text('nusuk_import_credentials')->nullable()->after('nusuk_import_implementation');
        });
    }

    public function down(): void
    {
        Schema::table('integration_settings', function (Blueprint $table) {
            $table->dropColumn([
                'ticketing_implementation',
                'ticketing_credentials',
                'visa_tracking_implementation',
                'visa_tracking_credentials',
                'iata_lookup_implementation',
                'iata_lookup_credentials',
                'nusuk_import_implementation',
                'nusuk_import_credentials',
            ]);
        });
    }
};
