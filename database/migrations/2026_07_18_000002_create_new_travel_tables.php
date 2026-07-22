<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // -----------------------------------------------------------------------
        // hotel_rooms — individual room records per hotel for allocation grid
        // -----------------------------------------------------------------------
        Schema::create('hotel_rooms', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('company_id')->unsigned()->nullable();
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->integer('hotel_id')->unsigned();
            $table->foreign('hotel_id')->references('id')->on('hotels')->onDelete('cascade');
            $table->string('room_number')->nullable();
            $table->string('room_type')->nullable();       // single, double, triple, quad, etc.
            $table->string('floor')->nullable();
            $table->unsignedTinyInteger('capacity')->default(2);  // max pax in this room
            $table->string('gender_restriction')->nullable();     // male, female, mixed
            $table->boolean('is_available')->default(true);
            $table->text('notes')->nullable();
            $table->integer('added_by')->unsigned()->nullable();
            $table->foreign('added_by')->references('id')->on('users')->onDelete('set null');
            $table->timestamps();
        });

        // -----------------------------------------------------------------------
        // room_allocations — passenger-to-room assignment
        // -----------------------------------------------------------------------
        Schema::create('room_allocations', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('hotel_room_id')->unsigned();
            $table->foreign('hotel_room_id')->references('id')->on('hotel_rooms')->onDelete('cascade');
            $table->integer('passenger_id')->unsigned();
            $table->foreign('passenger_id')->references('id')->on('passengers')->onDelete('cascade');
            $table->integer('booking_group_id')->unsigned()->nullable();
            $table->foreign('booking_group_id')->references('id')->on('booking_groups')->onDelete('set null');
            $table->date('check_in')->nullable();
            $table->date('check_out')->nullable();
            $table->enum('status', ['reserved', 'checked_in', 'checked_out', 'cancelled'])->default('reserved');
            $table->text('notes')->nullable();
            $table->integer('allocated_by')->unsigned()->nullable();
            $table->foreign('allocated_by')->references('id')->on('users')->onDelete('set null');
            $table->timestamps();
        });

        // -----------------------------------------------------------------------
        // insurance_policies — insurance product catalogue
        // -----------------------------------------------------------------------
        Schema::create('insurance_policies', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('company_id')->unsigned()->nullable();
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->string('provider_name');
            $table->string('policy_type');                 // e.g. travel, medical, hajj
            $table->string('policy_number')->nullable();
            $table->decimal('rate', 15, 2)->default(0);    // per-passenger rate
            $table->string('currency_code', 10)->default('PKR');
            $table->string('coverage_summary')->nullable();
            $table->text('terms_conditions')->nullable();
            $table->date('valid_from')->nullable();
            $table->date('valid_to')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('added_by')->unsigned()->nullable();
            $table->foreign('added_by')->references('id')->on('users')->onDelete('set null');
            $table->timestamps();
        });

        // -----------------------------------------------------------------------
        // insurance_sales — per-passenger insurance sale record
        // -----------------------------------------------------------------------
        Schema::create('insurance_sales', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('company_id')->unsigned()->nullable();
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->integer('passenger_id')->unsigned();
            $table->foreign('passenger_id')->references('id')->on('passengers')->onDelete('cascade');
            $table->integer('policy_id')->unsigned();
            $table->foreign('policy_id')->references('id')->on('insurance_policies')->onDelete('restrict');
            $table->integer('booking_group_id')->unsigned()->nullable();
            $table->foreign('booking_group_id')->references('id')->on('booking_groups')->onDelete('set null');
            $table->decimal('amount', 15, 2)->default(0);
            $table->string('currency_code', 10)->default('PKR');
            $table->integer('account_id')->unsigned()->nullable();   // posts to chart_of_accounts
            $table->foreign('account_id')->references('id')->on('chart_of_accounts')->onDelete('set null');
            $table->string('certificate_number')->nullable();
            $table->enum('status', ['active', 'claimed', 'cancelled'])->default('active');
            $table->integer('added_by')->unsigned()->nullable();
            $table->foreign('added_by')->references('id')->on('users')->onDelete('set null');
            $table->timestamps();
        });

        // -----------------------------------------------------------------------
        // cash_receipts — cash-specific inbound handling (separate from bank/account postings)
        // -----------------------------------------------------------------------
        Schema::create('cash_receipts', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('company_id')->unsigned()->nullable();
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->integer('account_id')->unsigned()->nullable();   // cash account in chart_of_accounts
            $table->foreign('account_id')->references('id')->on('chart_of_accounts')->onDelete('set null');
            $table->decimal('amount', 15, 2)->default(0);
            $table->string('currency_code', 10)->default('PKR');
            $table->date('date');
            $table->string('received_from');
            $table->string('reference_no')->nullable();
            $table->text('narration')->nullable();
            $table->integer('journal_voucher_id')->unsigned()->nullable();  // auto-created JV
            $table->foreign('journal_voucher_id')->references('id')->on('journal_vouchers')->onDelete('set null');
            $table->integer('added_by')->unsigned()->nullable();
            $table->foreign('added_by')->references('id')->on('users')->onDelete('set null');
            $table->integer('last_updated_by')->unsigned()->nullable();
            $table->foreign('last_updated_by')->references('id')->on('users')->onDelete('set null');
            $table->timestamps();
        });

        // -----------------------------------------------------------------------
        // travel_payments — Receive Payment / Make Payment (posts to journal_vouchers)
        // -----------------------------------------------------------------------
        Schema::create('travel_payments', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('company_id')->unsigned()->nullable();
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->enum('payment_direction', ['receive', 'make']);  // receive=inbound, make=outbound
            $table->integer('party_user_id')->unsigned()->nullable();    // customer or vendor user
            $table->foreign('party_user_id')->references('id')->on('users')->onDelete('set null');
            $table->integer('debit_account_id')->unsigned()->nullable();
            $table->foreign('debit_account_id')->references('id')->on('chart_of_accounts')->onDelete('set null');
            $table->integer('credit_account_id')->unsigned()->nullable();
            $table->foreign('credit_account_id')->references('id')->on('chart_of_accounts')->onDelete('set null');
            $table->decimal('amount', 15, 2);
            $table->string('currency_code', 10)->default('PKR');
            $table->decimal('exchange_rate', 15, 6)->default(1);
            $table->decimal('amount_base_currency', 15, 2)->default(0);
            $table->date('payment_date');
            $table->enum('payment_method', ['cash', 'bank_transfer', 'cheque', 'online'])->default('bank_transfer');
            $table->string('reference_no')->nullable();
            $table->string('cheque_no')->nullable();
            $table->text('narration')->nullable();
            $table->integer('booking_group_id')->unsigned()->nullable();  // optional link to booking
            $table->foreign('booking_group_id')->references('id')->on('booking_groups')->onDelete('set null');
            $table->integer('journal_voucher_id')->unsigned()->nullable();  // auto-created JV
            $table->foreign('journal_voucher_id')->references('id')->on('journal_vouchers')->onDelete('set null');
            $table->enum('status', ['draft', 'posted', 'cancelled'])->default('posted');
            $table->integer('added_by')->unsigned()->nullable();
            $table->foreign('added_by')->references('id')->on('users')->onDelete('set null');
            $table->integer('last_updated_by')->unsigned()->nullable();
            $table->foreign('last_updated_by')->references('id')->on('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('travel_payments');
        Schema::dropIfExists('cash_receipts');
        Schema::dropIfExists('insurance_sales');
        Schema::dropIfExists('insurance_policies');
        Schema::dropIfExists('room_allocations');
        Schema::dropIfExists('hotel_rooms');
    }
};
