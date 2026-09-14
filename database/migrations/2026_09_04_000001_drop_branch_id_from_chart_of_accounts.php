<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (Schema::hasColumn('chart_of_accounts', 'branch_id')) {
            Schema::table('chart_of_accounts', function (Blueprint $table) {
                $table->dropColumn('branch_id');
            });
        }
    }

    public function down()
    {
        if (!Schema::hasColumn('chart_of_accounts', 'branch_id')) {
            Schema::table('chart_of_accounts', function (Blueprint $table) {
                $table->integer('branch_id')->unsigned()->nullable();
            });
        }
    }
};
