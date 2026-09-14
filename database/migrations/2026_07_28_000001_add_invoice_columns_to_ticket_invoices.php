<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ticket_invoices', function (Blueprint $table) {
            // Add invoice_number column (unique, used by DataTable for ordering)
            if (!Schema::hasColumn('ticket_invoices', 'invoice_number')) {
                $table->string('invoice_number')->nullable()->unique()->after('id');
            }

            // Add date column (for DataTable)
            if (!Schema::hasColumn('ticket_invoices', 'date')) {
                $table->date('date')->nullable()->after('invoice_number');
            }

            // Add total_amount column (renamed from 'amount' for consistency with Controller)
            if (!Schema::hasColumn('ticket_invoices', 'total_amount')) {
                $table->decimal('total_amount', 15, 2)->default(0)->after('date');
            }

            // Add ticket_count column (for DataTable)
            if (!Schema::hasColumn('ticket_invoices', 'ticket_count')) {
                $table->integer('ticket_count')->default(1)->after('total_amount');
            }

            // Add status column (for DataTable)
            if (!Schema::hasColumn('ticket_invoices', 'status')) {
                $table->enum('status', ['pending', 'issued', 'refunded', 'cancelled'])->default('pending')->after('ticket_count');
            }

            // Add customer_id reference (for DataTable) - no foreign key to avoid constraint issues
            if (!Schema::hasColumn('ticket_invoices', 'customer_id')) {
                $table->integer('customer_id')->unsigned()->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('ticket_invoices', function (Blueprint $table) {
            $table->dropColumn([
                'invoice_number',
                'date',
                'total_amount',
                'ticket_count',
                'status',
                'customer_id',
            ]);
        });
    }
};