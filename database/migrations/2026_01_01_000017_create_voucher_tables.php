<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('vouchers', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('company_id')->unsigned()->nullable();
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->integer('booking_group_id')->unsigned()->nullable();
            $table->foreign('booking_group_id')->references('id')->on('booking_groups')->onDelete('cascade');
            $table->enum('type', ['accommodation', 'transport', 'full'])->default('full');
            $table->enum('status', ['draft', 'locked', 'issued'])->default('draft');
            $table->decimal('charges_total', 15, 2)->default(0);
            $table->string('pdf_path')->nullable();
            $table->string('voucher_number')->nullable();
            $table->integer('added_by')->unsigned()->nullable();
            $table->foreign('added_by')->references('id')->on('users')->onDelete('set null');
            $table->integer('last_updated_by')->unsigned()->nullable();
            $table->foreign('last_updated_by')->references('id')->on('users')->onDelete('set null');
            $table->timestamps();
        });

        Schema::create('voucher_charges', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('voucher_id')->unsigned();
            $table->foreign('voucher_id')->references('id')->on('vouchers')->onDelete('cascade');
            $table->string('description');
            $table->decimal('amount', 15, 2)->default(0);
            $table->integer('added_by')->unsigned()->nullable();
            $table->foreign('added_by')->references('id')->on('users')->onDelete('set null');
            $table->timestamps();
        });

        Schema::create('ticket_invoices', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('company_id')->unsigned()->nullable();
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->integer('booking_group_id')->unsigned()->nullable();
            $table->foreign('booking_group_id')->references('id')->on('booking_groups')->onDelete('set null');
            $table->integer('airline_id')->unsigned()->nullable();
            $table->foreign('airline_id')->references('id')->on('airlines')->onDelete('set null');
            $table->integer('passenger_id')->unsigned()->nullable();
            $table->foreign('passenger_id')->references('id')->on('passengers')->onDelete('set null');
            $table->decimal('amount', 15, 2)->default(0);
            $table->integer('sector_id')->unsigned()->nullable();
            $table->foreign('sector_id')->references('id')->on('sectors')->onDelete('set null');
            $table->string('ticket_number')->nullable();
            $table->integer('added_by')->unsigned()->nullable();
            $table->foreign('added_by')->references('id')->on('users')->onDelete('set null');
            $table->integer('last_updated_by')->unsigned()->nullable();
            $table->foreign('last_updated_by')->references('id')->on('users')->onDelete('set null');
            $table->timestamps();
        });

        Schema::create('ticket_refunds', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('ticket_invoice_id')->unsigned();
            $table->foreign('ticket_invoice_id')->references('id')->on('ticket_invoices')->onDelete('cascade');
            $table->decimal('refund_amount', 15, 2)->default(0);
            $table->text('reason')->nullable();
            $table->integer('added_by')->unsigned()->nullable();
            $table->foreign('added_by')->references('id')->on('users')->onDelete('set null');
            $table->timestamps();
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('user_id')->unsigned()->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->string('action');
            $table->string('entity_type');
            $table->integer('entity_id')->unsigned()->nullable();
            $table->json('before')->nullable();
            $table->json('after')->nullable();
            $table->string('ip_address')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('ticket_refunds');
        Schema::dropIfExists('ticket_invoices');
        Schema::dropIfExists('voucher_charges');
        Schema::dropIfExists('vouchers');
    }
};
