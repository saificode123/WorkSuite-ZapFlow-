<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('packages', function (Blueprint $table) {
            if (!Schema::hasColumn('packages', 'transporter_id')) {
                $table->integer('transporter_id')->unsigned()->nullable()->after('company_id');
                $table->foreign('transporter_id')->references('id')->on('transporters')->onDelete('set null');
            }
            if (!Schema::hasColumn('packages', 'visa_company_id')) {
                $table->integer('visa_company_id')->unsigned()->nullable()->after('transporter_id');
                $table->foreign('visa_company_id')->references('id')->on('visa_companies')->onDelete('set null');
            }
        });
    }

    public function down()
    {
        Schema::table('packages', function (Blueprint $table) {
            if (Schema::hasColumn('packages', 'transporter_id')) {
                $table->dropForeign(['transporter_id']);
                $table->dropColumn('transporter_id');
            }
            if (Schema::hasColumn('packages', 'visa_company_id')) {
                $table->dropForeign(['visa_company_id']);
                $table->dropColumn('visa_company_id');
            }
        });
    }
};
