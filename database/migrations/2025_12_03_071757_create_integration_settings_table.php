<?php
// FILE 1: Create Migration for integration_settings table
// database/migrations/2024_XX_XX_create_integration_settings_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('integration_settings', function (Blueprint $table) {
            $table->id();
            $table->integer('company_id')->unsigned()->nullable();
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade')->onUpdate('cascade');

            // Jira Settings
            $table->boolean('jira_status')->default(0);
            $table->string('jira_host')->nullable();
            $table->string('jira_user')->nullable();
            $table->text('jira_api_key')->nullable();

            // BugHerd Settings
            $table->boolean('bugherd_status')->default(0);
            $table->string('bugherd_base')->nullable();
            $table->text('bugherd_api_key')->nullable();
            $table->text('bugherd_webhook_token')->nullable();

            // Xero Settings
            $table->boolean('xero_status')->default(0);
            $table->string('xero_client_id')->nullable();
            $table->text('xero_client_secret')->nullable();
            $table->string('xero_redirect_uri')->nullable();
            $table->string('xero_scopes')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('integration_settings');
    }
};
