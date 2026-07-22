<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('flights', function (Blueprint $table) {
            $table->increments('id');
            $table->string('flight_number')->nullable();
            $table->string('airline')->nullable();
            $table->string('origin')->nullable();
            $table->string('destination')->nullable();
            $table->dateTime('departure_time')->nullable();
            $table->dateTime('arrival_time')->nullable();
            $table->integer('added_by')->unsigned()->nullable();
            $table->foreign('added_by')->references('id')->on('users')->onDelete('set null');
            $table->timestamps();
        });

        Schema::create('sectors', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('from_location')->nullable();
            $table->string('to_location')->nullable();
            $table->integer('added_by')->unsigned()->nullable();
            $table->foreign('added_by')->references('id')->on('users')->onDelete('set null');
            $table->timestamps();
        });

        Schema::create('relations', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->integer('added_by')->unsigned()->nullable();
            $table->foreign('added_by')->references('id')->on('users')->onDelete('set null');
            $table->timestamps();
        });

        Schema::create('airlines', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('company_id')->unsigned()->nullable();
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->string('name');
            $table->string('code')->nullable();
            $table->string('contact_person')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->integer('added_by')->unsigned()->nullable();
            $table->foreign('added_by')->references('id')->on('users')->onDelete('set null');
            $table->integer('last_updated_by')->unsigned()->nullable();
            $table->foreign('last_updated_by')->references('id')->on('users')->onDelete('set null');
            $table->timestamps();
        });

        Schema::create('airline_accounts', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('airline_id')->unsigned();
            $table->foreign('airline_id')->references('id')->on('airlines')->onDelete('cascade');
            $table->integer('account_id')->unsigned();
            $table->foreign('account_id')->references('id')->on('chart_of_accounts')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('airline_accounts');
        Schema::dropIfExists('airlines');
        Schema::dropIfExists('relations');
        Schema::dropIfExists('sectors');
        Schema::dropIfExists('flights');
    }
};
