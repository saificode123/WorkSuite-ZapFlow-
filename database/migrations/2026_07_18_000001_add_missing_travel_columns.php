<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // -----------------------------------------------------------------------
        // client_details — add credit_limit for B2B Agent credit management
        // -----------------------------------------------------------------------
        Schema::whenTableDoesntHaveColumn('client_details', 'credit_limit', function (Blueprint $table) {
            $table->decimal('credit_limit', 15, 2)->default(0)->after('customer_status');
        });

        // -----------------------------------------------------------------------
        // passengers — visa pipeline + mahram tracking
        // -----------------------------------------------------------------------
        Schema::whenTableDoesntHaveColumn('passengers', 'visa_pipeline_status', function (Blueprint $table) {
            $table->enum('visa_pipeline_status', [
                'draft',
                'sent_to_embassy',
                'mofa_received',
                'issued',
                'rejected',
            ])->default('draft')->after('mofa_status');
        });

        Schema::whenTableDoesntHaveColumn('passengers', 'visa_mofa_ref', function (Blueprint $table) {
            $table->string('visa_mofa_ref')->nullable()->after('visa_pipeline_status');
        });

        Schema::whenTableDoesntHaveColumn('passengers', 'visa_rejection_reason', function (Blueprint $table) {
            $table->text('visa_rejection_reason')->nullable()->after('visa_mofa_ref');
        });

        Schema::whenTableDoesntHaveColumn('passengers', 'visa_status_updated_by', function (Blueprint $table) {
            $table->integer('visa_status_updated_by')->unsigned()->nullable()->after('visa_rejection_reason');
            $table->foreign('visa_status_updated_by')->references('id')->on('users')->onDelete('set null');
        });

        Schema::whenTableDoesntHaveColumn('passengers', 'visa_status_updated_at', function (Blueprint $table) {
            $table->timestamp('visa_status_updated_at')->nullable()->after('visa_status_updated_by');
        });

        Schema::whenTableDoesntHaveColumn('passengers', 'room_allocation_id', function (Blueprint $table) {
            $table->integer('room_allocation_id')->unsigned()->nullable()->after('mahram_passenger_id');
        });

        // -----------------------------------------------------------------------
        // ticket_invoices — sale type (BSP/XO/direct) + PNR
        // -----------------------------------------------------------------------
        Schema::whenTableDoesntHaveColumn('ticket_invoices', 'sale_type', function (Blueprint $table) {
            $table->enum('sale_type', ['bsp', 'xo', 'direct'])->default('direct')->after('ticket_number');
        });

        Schema::whenTableDoesntHaveColumn('ticket_invoices', 'pnr', function (Blueprint $table) {
            $table->string('pnr')->nullable()->after('sale_type');
        });

        Schema::whenTableDoesntHaveColumn('ticket_invoices', 'cost_amount', function (Blueprint $table) {
            $table->decimal('cost_amount', 15, 2)->default(0)->after('amount');
        });

        Schema::whenTableDoesntHaveColumn('ticket_invoices', 'currency_code', function (Blueprint $table) {
            $table->string('currency_code', 10)->default('PKR')->after('cost_amount');
        });

        Schema::whenTableDoesntHaveColumn('ticket_invoices', 'status', function (Blueprint $table) {
            $table->enum('status', ['pending', 'issued', 'refunded', 'cancelled'])->default('pending')->after('currency_code');
        });

        // -----------------------------------------------------------------------
        // vouchers — QR code, version for immutability, locked_at timestamp
        // -----------------------------------------------------------------------
        Schema::whenTableDoesntHaveColumn('vouchers', 'qr_code', function (Blueprint $table) {
            $table->string('qr_code')->nullable()->after('pdf_path');
        });

        Schema::whenTableDoesntHaveColumn('vouchers', 'qr_payload', function (Blueprint $table) {
            $table->string('qr_payload')->nullable()->after('qr_code');
        });

        Schema::whenTableDoesntHaveColumn('vouchers', 'version', function (Blueprint $table) {
            $table->unsignedTinyInteger('version')->default(1)->after('qr_payload');
        });

        Schema::whenTableDoesntHaveColumn('vouchers', 'locked_at', function (Blueprint $table) {
            $table->timestamp('locked_at')->nullable()->after('version');
        });

        Schema::whenTableDoesntHaveColumn('vouchers', 'locked_by', function (Blueprint $table) {
            $table->integer('locked_by')->unsigned()->nullable()->after('locked_at');
            $table->foreign('locked_by')->references('id')->on('users')->onDelete('set null');
        });

        // -----------------------------------------------------------------------
        // visa_companies — missing fields (approval rates, logo, footer)
        // -----------------------------------------------------------------------
        Schema::whenTableDoesntHaveColumn('visa_companies', 'logo_path', function (Blueprint $table) {
            $table->string('logo_path')->nullable()->after('name');
        });

        Schema::whenTableDoesntHaveColumn('visa_companies', 'voucher_footer_text', function (Blueprint $table) {
            $table->text('voucher_footer_text')->nullable()->after('logo_path');
        });

        Schema::whenTableDoesntHaveColumn('visa_companies', 'voucher_footer_image', function (Blueprint $table) {
            $table->string('voucher_footer_image')->nullable()->after('voucher_footer_text');
        });

        Schema::whenTableDoesntHaveColumn('visa_companies', 'approval_sale_rate', function (Blueprint $table) {
            $table->decimal('approval_sale_rate', 15, 2)->default(0)->after('voucher_footer_image');
        });

        Schema::whenTableDoesntHaveColumn('visa_companies', 'approval_cost_rate', function (Blueprint $table) {
            $table->decimal('approval_cost_rate', 15, 2)->default(0)->after('approval_sale_rate');
        });

        Schema::whenTableDoesntHaveColumn('visa_companies', 'contact_person', function (Blueprint $table) {
            $table->string('contact_person')->nullable()->after('approval_cost_rate');
        });

        Schema::whenTableDoesntHaveColumn('visa_companies', 'phone', function (Blueprint $table) {
            $table->string('phone')->nullable()->after('contact_person');
        });

        Schema::whenTableDoesntHaveColumn('visa_companies', 'email', function (Blueprint $table) {
            $table->string('email')->nullable()->after('phone');
        });

        Schema::whenTableDoesntHaveColumn('visa_companies', 'address', function (Blueprint $table) {
            $table->text('address')->nullable()->after('email');
        });

        Schema::whenTableDoesntHaveColumn('visa_companies', 'added_by', function (Blueprint $table) {
            $table->integer('added_by')->unsigned()->nullable()->after('address');
            $table->foreign('added_by')->references('id')->on('users')->onDelete('set null');
        });

        Schema::whenTableDoesntHaveColumn('visa_companies', 'last_updated_by', function (Blueprint $table) {
            $table->integer('last_updated_by')->unsigned()->nullable()->after('added_by');
            $table->foreign('last_updated_by')->references('id')->on('users')->onDelete('set null');
        });

        // -----------------------------------------------------------------------
        // booking_groups — add status column and group_leader if missing
        // -----------------------------------------------------------------------
        Schema::whenTableDoesntHaveColumn('booking_groups', 'status', function (Blueprint $table) {
            $table->enum('status', ['pending', 'confirmed', 'cancelled', 'completed'])->default('pending')->after('notes');
        });

        Schema::whenTableDoesntHaveColumn('booking_groups', 'total_pax', function (Blueprint $table) {
            $table->unsignedInteger('total_pax')->default(0)->after('status');
        });

        Schema::whenTableDoesntHaveColumn('booking_groups', 'group_leader', function (Blueprint $table) {
            $table->string('group_leader')->nullable()->after('group_name');
        });

        Schema::whenTableDoesntHaveColumn('booking_groups', 'visa_company_id', function (Blueprint $table) {
            $table->integer('visa_company_id')->unsigned()->nullable()->after('iata_id');
            $table->foreign('visa_company_id')->references('id')->on('visa_companies')->onDelete('set null');
        });

        // -----------------------------------------------------------------------
        // packages — add missing calculation fields
        // -----------------------------------------------------------------------
        Schema::whenTableDoesntHaveColumn('packages', 'cost_price', function (Blueprint $table) {
            $table->decimal('cost_price', 15, 2)->default(0)->after('price');
        });

        Schema::whenTableDoesntHaveColumn('packages', 'markup_percentage', function (Blueprint $table) {
            $table->decimal('markup_percentage', 8, 4)->default(0)->after('cost_price');
        });

        Schema::whenTableDoesntHaveColumn('packages', 'is_price_auto_calculated', function (Blueprint $table) {
            $table->boolean('is_price_auto_calculated')->default(true)->after('markup_percentage');
        });

        Schema::whenTableDoesntHaveColumn('packages', 'season', function (Blueprint $table) {
            $table->enum('season', ['regular', 'ramadan', 'shawwal', 'hajj'])->default('regular')->after('is_price_auto_calculated');
        });

        // -----------------------------------------------------------------------
        // chart_of_accounts — add branch restriction support
        // -----------------------------------------------------------------------
        Schema::whenTableDoesntHaveColumn('chart_of_accounts', 'branch_id', function (Blueprint $table) {
            $table->integer('branch_id')->unsigned()->nullable()->after('is_bank_account');
        });

        Schema::whenTableDoesntHaveColumn('chart_of_accounts', 'currency_code', function (Blueprint $table) {
            $table->string('currency_code', 10)->default('PKR')->after('branch_id');
        });

        Schema::whenTableDoesntHaveColumn('chart_of_accounts', 'is_system_account', function (Blueprint $table) {
            $table->boolean('is_system_account')->default(false)->after('currency_code');
        });
    }

    public function down(): void
    {
        // Column drops omitted for safety — use fresh migration in dev
    }
};
