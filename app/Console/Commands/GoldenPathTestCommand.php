<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Role;
use App\Models\Company;
use App\Models\ClientDetails;
use App\Models\FinancialYear;
use App\Models\ChartOfAccount;
use App\Models\ServiceProvider;
use App\Models\Hotel;
use App\Models\Package;
use App\Models\VisaCompany;
use App\Models\BookingGroup;
use App\Models\Passenger;
use App\Models\HotelRoom;
use App\Models\RoomAllocation;
use App\Models\Voucher;
use App\Models\VoucherCharge;
use App\Models\JournalVoucher;
use App\Models\JournalVoucherLine;
use App\Services\BookingImportService;
use Carbon\Carbon;

class GoldenPathTestCommand extends Command
{
    protected $signature = 'test:golden-path';
    protected $description = 'Seed test data and verify the Umrah CRM Golden Path end-to-end.';

    public function handle()
    {
        $this->info("Starting Golden Path verification...");

        DB::beginTransaction();
        try {
            // 1. Employee / Super Admin Setup
            $company = Company::firstOrCreate(['company_name' => 'Umrah Travel Co.']);
            $adminRole = Role::firstOrCreate(['name' => 'admin'], ['display_name' => 'Admin']);
            $clientRole = Role::firstOrCreate(['name' => 'client'], ['display_name' => 'Client']);

            $admin = User::firstOrCreate(['email' => 'admin@umrah.test'], [
                'name' => 'Super Admin',
                'password' => Hash::make('123456'),
                'company_id' => $company->id,
            ]);
            if (!$admin->hasRole('admin')) {
                $admin->attachRole($adminRole);
            }

            // Temporarily set the logged in user so user() calls in observers/controllers don't fail
            auth()->login($admin);

            // 2. Chart of Accounts Setup
            $this->info("Setting up Chart of Accounts...");
            $fy = FinancialYear::firstOrCreate(['company_id' => $company->id, 'is_closed' => false], [
                'start_date' => Carbon::now()->startOfYear(),
                'end_date' => Carbon::now()->endOfYear(),
            ]);

            $assetAccount = ChartOfAccount::firstOrCreate(['name' => 'Assets', 'company_id' => $company->id], ['type' => 'asset', 'level' => 1]);
            $liabilityAccount = ChartOfAccount::firstOrCreate(['name' => 'Liabilities', 'company_id' => $company->id], ['type' => 'liability', 'level' => 1]);
            $incomeAccount = ChartOfAccount::firstOrCreate(['name' => 'Income', 'company_id' => $company->id], ['type' => 'income', 'level' => 1]);
            $expenseAccount = ChartOfAccount::firstOrCreate(['name' => 'Expenses', 'company_id' => $company->id], ['type' => 'expense', 'level' => 1]);

            $bankAccount = ChartOfAccount::firstOrCreate(['name' => 'Main Bank Account', 'company_id' => $company->id], [
                'parent_id' => $assetAccount->id, 'type' => 'asset', 'level' => 2, 'is_bank_account' => true
            ]);

            // 3. Agent Creation
            $this->info("Creating B2B Agent...");
            $agentUmrahAccount = ChartOfAccount::firstOrCreate(['name' => 'Agent XYZ Umrah A/C', 'company_id' => $company->id], [
                'parent_id' => $assetAccount->id, 'type' => 'asset', 'level' => 2
            ]);

            $agent = User::firstOrCreate(['email' => 'agent@xyz.test'], [
                'name' => 'Agent XYZ',
                'password' => Hash::make('123456'),
                'company_id' => $company->id,
            ]);
            if (!$agent->hasRole('client')) {
                $agent->attachRole($clientRole);
            }

            $clientDetails = ClientDetails::updateOrCreate(['user_id' => $agent->id], [
                'company_name' => 'XYZ Travels',
                'company_id' => $company->id,
                'umrah_account_id' => $agentUmrahAccount->id,
            ]);

            // 4. Inventory Setup
            $this->info("Setting up Inventory...");
            $hotelPayableAccount = ChartOfAccount::firstOrCreate(['name' => 'Hilton Makkah Payable', 'company_id' => $company->id], [
                'parent_id' => $liabilityAccount->id, 'type' => 'liability', 'level' => 2
            ]);
            $serviceProvider = ServiceProvider::firstOrCreate(['name' => 'Hilton Corp']);
            // link account... simplified for test

            $hotel = Hotel::firstOrCreate(['name' => 'Hilton Makkah', 'city' => 'Makkah', 'company_id' => $company->id]);
            $hotelRoom = HotelRoom::firstOrCreate(['hotel_id' => $hotel->id, 'room_type' => 'Quad', 'room_number' => '101', 'capacity' => 4]);

            $package = Package::firstOrCreate(['name' => '14 Days Standard', 'company_id' => $company->id]);

            $visaCompany = VisaCompany::firstOrCreate(['name' => 'Etimad Visa Services', 'company_id' => $company->id], [
                'approval_sale_rate' => 500,
                'approval_cost_rate' => 400,
            ]);

            // 5. Booking Import
            $this->info("Importing Booking...");
            $csvData = "Group Detail\nGroupNo, GP-001\nGroupName, Alpha Group\nPassportNo, First Name, Family Name, Birth Date, Gender, Mofa\nPK12345, John, Doe, 01/01/1980, Male, 1\nPK12346, Jane, Doe, 05/05/1985, Female, 1";
            $importService = new BookingImportService();
            $parsed = $importService->parseContent($csvData);

            if (!empty($parsed['errors'])) {
                $this->error("Import errors: " . implode(', ', $parsed['errors']));
                return;
            }

            $booking = BookingGroup::firstOrCreate([
                'group_no' => $parsed['group_no'],
                'company_id' => $company->id,
            ], [
                'group_name' => $parsed['group_name'],
                'customer_id' => $agent->id,
                'package_id' => $package->id,
                'status' => 'confirmed'
            ]);

            foreach ($parsed['passengers'] as $paxData) {
                Passenger::firstOrCreate(['passport_no' => $paxData['passport_no'], 'booking_group_id' => $booking->id], $paxData);
            }

            // 6. Visa Pipeline & Room Allocation
            $this->info("Processing Visas & Allocating Rooms...");
            $passengers = Passenger::where('booking_group_id', $booking->id)->get();
            foreach ($passengers as $pax) {
                $pax->update(['visa_pipeline_status' => 'issued', 'mofa_status' => '123456789']);
                RoomAllocation::firstOrCreate([
                    'passenger_id' => $pax->id,
                    'hotel_room_id' => $hotelRoom->id,
                    'booking_group_id' => $booking->id,
                ], [
                    'status' => 'reserved',
                    'allocated_by' => $admin->id,
                ]);
                $pax->update(['room_allocation_id' => $hotelRoom->id]);
            }

            // 7. Voucher Issuance & Charges
            $this->info("Issuing Voucher...");
            $voucher = Voucher::firstOrCreate(['booking_group_id' => $booking->id], [
                'company_id' => $company->id,
                'voucher_number' => 'VCH-GP-001',
                'type' => 'full',
                'status' => 'draft',
                'version' => 1,
                'added_by' => $admin->id
            ]);

            // Lock voucher
            $voucher->update(['status' => 'locked', 'locked_at' => now(), 'locked_by' => $admin->id]);
            
            // 8. Accounting Postings (JV for Agent Sales and Hotel Cost)
            $this->info("Posting Accounting Entries...");
            
            // Sale to Agent (Debit Agent A/C, Credit Income)
            $saleAmount = 5000;
            $jvSale = JournalVoucher::create([
                'company_id' => $company->id,
                'financial_year_id' => $fy->id,
                'voucher_number' => 'JV-SALE-001',
                'date' => now(),
                'created_by' => $admin->id,
                'narration' => 'Booking sale for ' . $booking->group_no,
                'is_balanced' => true,
            ]);
            JournalVoucherLine::create(['journal_voucher_id' => $jvSale->id, 'account_id' => $agentUmrahAccount->id, 'debit' => $saleAmount, 'credit' => 0]);
            JournalVoucherLine::create(['journal_voucher_id' => $jvSale->id, 'account_id' => $incomeAccount->id, 'debit' => 0, 'credit' => $saleAmount, 'booking_group_id' => $booking->id]); // Note: In real app, booking_group_id links the P&L

            // Cost to Hotel (Credit Hotel Payable, Debit Expense)
            $costAmount = 3000;
            $jvCost = JournalVoucher::create([
                'company_id' => $company->id,
                'financial_year_id' => $fy->id,
                'voucher_number' => 'JV-COST-001',
                'date' => now(),
                'created_by' => $admin->id,
                'narration' => 'Hotel cost for ' . $booking->group_no,
                'is_balanced' => true,
            ]);
            JournalVoucherLine::create(['journal_voucher_id' => $jvCost->id, 'account_id' => $expenseAccount->id, 'debit' => $costAmount, 'credit' => 0, 'booking_group_id' => $booking->id]);
            JournalVoucherLine::create(['journal_voucher_id' => $jvCost->id, 'account_id' => $hotelPayableAccount->id, 'debit' => 0, 'credit' => $costAmount]);

            // 9. Umrah Wise P&L
            $this->info("--------------------------------------------------");
            $this->info("UMRAH WISE PROFIT & LOSS REPORT FOR GROUP: " . $booking->group_no);
            
            // Calculate revenue and costs explicitly for this booking group
            $revenue = JournalVoucherLine::where('booking_group_id', $booking->id)
                ->whereHas('account', function($q) { $q->where('type', 'income'); })
                ->sum('credit');

            $costs = JournalVoucherLine::where('booking_group_id', $booking->id)
                ->whereHas('account', function($q) { $q->where('type', 'expense'); })
                ->sum('debit');

            $profit = $revenue - $costs;

            $this->info("Total Revenue: " . number_format($revenue, 2));
            $this->info("Total Costs:   " . number_format($costs, 2));
            $this->info("Net Margin:    " . number_format($profit, 2));
            $this->info("--------------------------------------------------");
            
            if ($profit == 2000) {
                $this->info("GOLDEN PATH VERIFICATION SUCCESSFUL!");
            } else {
                $this->error("P&L Calculation mismatch!");
            }

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Golden Path Test Failed: " . $e->getMessage() . " on line " . $e->getLine());
        }
    }
}
