<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visa_logs', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('company_id')->unsigned()->nullable();
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->integer('passenger_id')->unsigned();
            $table->foreign('passenger_id')->references('id')->on('passengers')->onDelete('cascade');
            $table->string('from_status')->nullable();
            $table->string('to_status');
            $table->integer('changed_by')->unsigned()->nullable();
            $table->foreign('changed_by')->references('id')->on('users')->onDelete('set null');
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visa_logs');
    }
};
