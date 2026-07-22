<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::whenTableDoesntHaveColumn('client_details', 'customer_type_id', function (Blueprint $table) {
            $table->integer('customer_type_id')->unsigned()->nullable()->after('sub_category_id');
            $table->foreign('customer_type_id')->references('id')->on('customer_types')->onDelete('set null');
        });

        Schema::whenTableDoesntHaveColumn('client_details', 'parent_customer_id', function (Blueprint $table) {
            $table->integer('parent_customer_id')->unsigned()->nullable()->after('customer_type_id');
        });

        Schema::whenTableDoesntHaveColumn('client_details', 'umrah_account_id', function (Blueprint $table) {
            $table->integer('umrah_account_id')->unsigned()->nullable()->after('parent_customer_id');
            $table->foreign('umrah_account_id')->references('id')->on('chart_of_accounts')->onDelete('set null');
        });

        Schema::whenTableDoesntHaveColumn('client_details', 'ticket_account_id', function (Blueprint $table) {
            $table->integer('ticket_account_id')->unsigned()->nullable()->after('umrah_account_id');
            $table->foreign('ticket_account_id')->references('id')->on('chart_of_accounts')->onDelete('set null');
        });

        Schema::whenTableDoesntHaveColumn('client_details', 'customer_status', function (Blueprint $table) {
            $table->enum('customer_status', ['active', 'blocked'])->default('active')->after('ticket_account_id');
        });
    }

    public function down()
    {
    }
};
