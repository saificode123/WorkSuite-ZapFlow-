<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('booking_groups', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('company_id')->unsigned()->nullable();
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->string('group_no')->nullable();
            $table->string('group_name');
            $table->integer('customer_id')->unsigned()->nullable();
            $table->foreign('customer_id')->references('id')->on('users')->onDelete('set null');
            $table->integer('package_id')->unsigned()->nullable();
            $table->foreign('package_id')->references('id')->on('packages')->onDelete('set null');
            $table->integer('iata_id')->unsigned()->nullable();
            $table->foreign('iata_id')->references('id')->on('iata_records')->onDelete('set null');
            $table->date('departure_date')->nullable();
            $table->date('return_date')->nullable();
            $table->text('notes')->nullable();
            $table->integer('added_by')->unsigned()->nullable();
            $table->foreign('added_by')->references('id')->on('users')->onDelete('set null');
            $table->integer('last_updated_by')->unsigned()->nullable();
            $table->foreign('last_updated_by')->references('id')->on('users')->onDelete('set null');
            $table->timestamps();
        });

        Schema::create('passengers', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('booking_group_id')->unsigned();
            $table->foreign('booking_group_id')->references('id')->on('booking_groups')->onDelete('cascade');
            $table->string('passport_no');
            $table->string('first_name');
            $table->string('family_name');
            $table->date('birth_date');
            $table->enum('gender', ['Male', 'Female'])->nullable();
            $table->string('mofa_status')->nullable()->default('0');
            $table->integer('relation_id')->unsigned()->nullable();
            $table->foreign('relation_id')->references('id')->on('relations')->onDelete('set null');
            $table->integer('mahram_passenger_id')->unsigned()->nullable();
            $table->integer('added_by')->unsigned()->nullable();
            $table->foreign('added_by')->references('id')->on('users')->onDelete('set null');
            $table->integer('last_updated_by')->unsigned()->nullable();
            $table->foreign('last_updated_by')->references('id')->on('users')->onDelete('set null');
            $table->timestamps();
        });

        Schema::create('booking_charges', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('booking_group_id')->unsigned();
            $table->foreign('booking_group_id')->references('id')->on('booking_groups')->onDelete('cascade');
            $table->string('charge_type');
            $table->decimal('amount', 15, 2)->default(0);
            $table->integer('account_id')->unsigned()->nullable();
            $table->foreign('account_id')->references('id')->on('chart_of_accounts')->onDelete('set null');
            $table->text('description')->nullable();
            $table->integer('added_by')->unsigned()->nullable();
            $table->foreign('added_by')->references('id')->on('users')->onDelete('set null');
            $table->timestamps();
        });

        Schema::create('repeat_fees', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('passenger_id')->unsigned();
            $table->foreign('passenger_id')->references('id')->on('passengers')->onDelete('cascade');
            $table->decimal('fee_amount', 15, 2)->default(0);
            $table->string('rule_applied')->nullable();
            $table->integer('added_by')->unsigned()->nullable();
            $table->foreign('added_by')->references('id')->on('users')->onDelete('set null');
            $table->timestamps();
        });

        Schema::create('passport_deliveries', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('passenger_id')->unsigned();
            $table->foreign('passenger_id')->references('id')->on('passengers')->onDelete('cascade');
            $table->enum('status', ['received', 'sent_for_processing', 'returned'])->default('received');
            $table->timestamp('timestamp')->useCurrent();
            $table->integer('handled_by')->unsigned()->nullable();
            $table->foreign('handled_by')->references('id')->on('users')->onDelete('set null');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('mutamer_transfers', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('passenger_id')->unsigned();
            $table->foreign('passenger_id')->references('id')->on('passengers')->onDelete('cascade');
            $table->integer('from_booking_group_id')->unsigned()->nullable();
            $table->foreign('from_booking_group_id')->references('id')->on('booking_groups')->onDelete('set null');
            $table->integer('to_booking_group_id')->unsigned()->nullable();
            $table->foreign('to_booking_group_id')->references('id')->on('booking_groups')->onDelete('set null');
            $table->integer('transferred_by')->unsigned()->nullable();
            $table->foreign('transferred_by')->references('id')->on('users')->onDelete('set null');
            $table->timestamp('transferred_at')->useCurrent();
            $table->text('reason')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('mutamer_transfers');
        Schema::dropIfExists('passport_deliveries');
        Schema::dropIfExists('repeat_fees');
        Schema::dropIfExists('booking_charges');
        Schema::dropIfExists('passengers');
        Schema::dropIfExists('booking_groups');
    }
};
