<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('transporters', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('company_id')->unsigned()->nullable();
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->string('name');
            $table->string('contact_person')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->integer('added_by')->unsigned()->nullable();
            $table->foreign('added_by')->references('id')->on('users')->onDelete('set null');
            $table->integer('last_updated_by')->unsigned()->nullable();
            $table->foreign('last_updated_by')->references('id')->on('users')->onDelete('set null');
            $table->timestamps();
        });

        Schema::create('transporter_accounts', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('transporter_id')->unsigned();
            $table->foreign('transporter_id')->references('id')->on('transporters')->onDelete('cascade');
            $table->integer('account_id')->unsigned();
            $table->foreign('account_id')->references('id')->on('chart_of_accounts')->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('transport_types', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('transport_routes', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('from_location')->nullable();
            $table->string('to_location')->nullable();
            $table->timestamps();
        });

        Schema::create('transport_rates', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('transporter_id')->unsigned();
            $table->foreign('transporter_id')->references('id')->on('transporters')->onDelete('cascade');
            $table->integer('transport_type_id')->unsigned()->nullable();
            $table->foreign('transport_type_id')->references('id')->on('transport_types')->onDelete('set null');
            $table->integer('route_id')->unsigned()->nullable();
            $table->foreign('route_id')->references('id')->on('transport_routes')->onDelete('set null');
            $table->decimal('rate', 15, 2)->default(0);
            $table->boolean('is_ziarat')->default(false);
            $table->integer('added_by')->unsigned()->nullable();
            $table->foreign('added_by')->references('id')->on('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('transport_rates');
        Schema::dropIfExists('transport_routes');
        Schema::dropIfExists('transport_types');
        Schema::dropIfExists('transporter_accounts');
        Schema::dropIfExists('transporters');
    }
};
