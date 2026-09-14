<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('other_service_invoices', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('company_id')->unsigned()->nullable();
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->string('invoice_number');
            $table->date('issue_date');
            $table->date('due_date')->nullable();
            $table->integer('customer_id')->unsigned()->nullable();
            $table->foreign('customer_id')->references('id')->on('users')->onDelete('set null');
            $table->string('service_type')->default('other');
            $table->text('description')->nullable();
            $table->decimal('amount', 15, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->integer('debit_account_id')->unsigned()->nullable();
            $table->foreign('debit_account_id')->references('id')->on('chart_of_accounts')->onDelete('set null');
            $table->integer('credit_account_id')->unsigned()->nullable();
            $table->foreign('credit_account_id')->references('id')->on('chart_of_accounts')->onDelete('set null');
            $table->integer('journal_voucher_id')->unsigned()->nullable();
            $table->foreign('journal_voucher_id')->references('id')->on('journal_vouchers')->onDelete('set null');
            $table->enum('status', ['pending', 'paid', 'cancelled'])->default('pending');
            $table->text('notes')->nullable();
            $table->integer('added_by')->unsigned()->nullable();
            $table->foreign('added_by')->references('id')->on('users')->onDelete('set null');
            $table->integer('last_updated_by')->unsigned()->nullable();
            $table->foreign('last_updated_by')->references('id')->on('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('other_service_invoices');
    }
};
