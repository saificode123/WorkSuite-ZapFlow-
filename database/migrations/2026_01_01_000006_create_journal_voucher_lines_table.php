<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('journal_voucher_lines', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('journal_voucher_id')->unsigned();
            $table->foreign('journal_voucher_id')->references('id')->on('journal_vouchers')->onDelete('cascade');
            $table->integer('account_id')->unsigned();
            $table->foreign('account_id')->references('id')->on('chart_of_accounts')->onDelete('cascade');
            $table->decimal('debit', 15, 2)->default(0);
            $table->decimal('credit', 15, 2)->default(0);
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('journal_voucher_lines');
    }
};
