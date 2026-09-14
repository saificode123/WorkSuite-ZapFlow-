<?php

namespace App\Http\Controllers;

use App\Models\ChartOfAccount;
use App\Models\JournalVoucher;
use App\Models\JournalVoucherLine;
use App\Models\BookingGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * TravelReportController
 *
 * Generates Trial Balance, Profit & Loss (full + monthly breakdown),
 * Ageing, Receivables, Payables, and per-Umrah group P&L reports.
 */
class TravelReportController extends AccountBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.travelReports';

        $this->middleware(function ($request, $next) {
            abort_403(!in_array('accounts', $this->user->modules));
            return $next($request);
        });
    }

    // ── Trial Balance ──────────────────────────────────────────────────────────
    public function trialBalance(Request $request)
    {
        $fromDate = $request->from_date ?? now()->startOfYear()->toDateString();
        $toDate   = $request->to_date   ?? now()->toDateString();

        // Sum debits and credits per account within the date range
        $balances = DB::table('journal_voucher_lines as jvl')
            ->join('journal_vouchers as jv', 'jv.id', '=', 'jvl.journal_voucher_id')
            ->join('chart_of_accounts as coa', 'coa.id', '=', 'jvl.account_id')
            ->where('jv.is_balanced', 1)
            ->whereBetween('jv.date', [$fromDate, $toDate])
            ->groupBy('coa.id', 'coa.code', 'coa.name', 'coa.type')
            ->select([
                'coa.id', 'coa.code', 'coa.name', 'coa.type',
                DB::raw("(CASE WHEN coa.type IN ('asset', 'expense') THEN 'debit' ELSE 'credit' END) as normal_balance"),
                DB::raw('SUM(jvl.debit) as total_debit'),
                DB::raw('SUM(jvl.credit) as total_credit'),
            ])
            ->orderBy('coa.code')
            ->get();

        $this->balances   = $balances;
        $this->fromDate   = $fromDate;
        $this->toDate     = $toDate;
        $this->totalDebit = (float) $balances->sum('total_debit');
        $this->totalCredit= (float) $balances->sum('total_credit');

        return view('travel.reports.trial-balance', $this->data);
    }

    // ── Profit & Loss ──────────────────────────────────────────────────────────
    public function profitLoss(Request $request)
    {
        $fromDate = $request->from_date ?? now()->startOfYear()->toDateString();
        $toDate   = $request->to_date   ?? now()->toDateString();

        [$revenue, $cogs, $expenses, $otherIncome] = $this->fetchPLAccounts($fromDate, $toDate);

        $grossProfit  = $revenue->sum('net') - $cogs->sum('net');
        $netProfit    = $grossProfit - $expenses->sum('net') + $otherIncome->sum('net');

        $this->fromDate    = $fromDate;
        $this->toDate      = $toDate;
        $this->revenue     = $revenue;
        $this->cogs        = $cogs;
        $this->expenses    = $expenses;
        $this->otherIncome = $otherIncome;
        $this->grossProfit = $grossProfit;
        $this->netProfit   = $netProfit;

        return view('travel.reports.profit-loss', $this->data);
    }

    // ── Monthly P&L Breakdown ─────────────────────────────────────────────────
    public function monthlyProfitLoss(Request $request)
    {
        $year = $request->year ?? now()->year;

        $monthly = DB::table('journal_voucher_lines as jvl')
            ->join('journal_vouchers as jv', 'jv.id', '=', 'jvl.journal_voucher_id')
            ->join('chart_of_accounts as coa', 'coa.id', '=', 'jvl.account_id')
            ->where('jv.is_balanced', 1)
            ->whereYear('jv.date', $year)
            ->whereIn('coa.type', ['income', 'expense'])
            ->groupBy(DB::raw('MONTH(jv.date)'), 'coa.type')
            ->select([
                DB::raw('MONTH(jv.date) as month'),
                'coa.type',
                DB::raw('SUM(jvl.credit) - SUM(jvl.debit) as net'),
            ])
            ->orderBy('month')
            ->get();

        $this->year    = $year;
        $this->monthly = $monthly;
        return view('travel.reports.monthly-pl', $this->data);
    }

    // ── Ageing Report ──────────────────────────────────────────────────────────
    public function ageing(Request $request)
    {
        $asOfDate = $request->as_of_date ?? now()->toDateString();
        $type     = $request->type ?? 'receivable'; // receivable | payable

        // Simplified ageing from travel_payments
        $this->rows   = DB::table('travel_payments as tp')
            ->join('users as u', 'u.id', '=', 'tp.party_user_id')
            ->where('tp.payment_direction', $type === 'receivable' ? 'receive' : 'make')
            ->where('tp.status', 'posted')
            ->select(['u.name', DB::raw('SUM(tp.amount) as total'), 'tp.payment_date'])
            ->groupBy('tp.party_user_id', 'u.name', 'tp.payment_date')
            ->get();

        $this->asOfDate = $asOfDate;
        $this->type     = $type;
        return view('travel.reports.ageing', $this->data);
    }

    // ── Receivables ─────────────────────────────────────────────────────────────
    public function receivables(Request $request)
    {
        $this->payments = \App\Models\TravelPayment::where('payment_direction', 'receive')
            ->with(['partyUser', 'bookingGroup'])
            ->when($request->from_date, fn($q) => $q->whereDate('payment_date', '>=', $request->from_date))
            ->when($request->to_date,   fn($q) => $q->whereDate('payment_date', '<=', $request->to_date))
            ->latest('payment_date')->paginate(30);
        return view('travel.reports.receivables', $this->data);
    }

    // ── Payables ────────────────────────────────────────────────────────────────
    public function payables(Request $request)
    {
        $this->payments = \App\Models\TravelPayment::where('payment_direction', 'make')
            ->with(['partyUser', 'bookingGroup'])
            ->when($request->from_date, fn($q) => $q->whereDate('payment_date', '>=', $request->from_date))
            ->when($request->to_date,   fn($q) => $q->whereDate('payment_date', '<=', $request->to_date))
            ->latest('payment_date')->paginate(30);
        return view('travel.reports.payables', $this->data);
    }

    // ── Umrah-wise P&L ───────────────────────────────────────────────────────
    public function umrahWisePl(Request $request)
    {
        $calculator = new \App\Services\Reports\UmrahPlCalculator();

        // calculateAll() runs two aggregated SQL queries (not N+1).
        $plByGroup = collect($calculator->calculateAll(company()->id ?? null))
            ->keyBy('booking_group_id');

        // Load the booking groups for display, paginated.
        $this->paginator = BookingGroup::with(['package', 'passengers'])
            ->orderByDesc('departure_date')
            ->paginate(20);

        // Attach the pre-computed P&L PlResult to each group.
        $this->items = $this->paginator->getCollection()->map(function (BookingGroup $bg) use ($plByGroup) {
            return [
                'booking' => $bg,
                'pl'      => $plByGroup[$bg->id]['pl'] ?? new \App\Services\Reports\PlResult(0.0, 0.0, 0.0),
            ];
        });

        return view('travel.reports.umrah-wise-pl', $this->data);
    }

    // ── Export ────────────────────────────────────────────────────────────────
    public function export(Request $request, string $report)
    {
        $validReports = ['trial-balance', 'profit-loss', 'monthly-pl', 'ageing', 'receivables', 'payables', 'umrah-wise-pl'];

        if (!in_array($report, $validReports)) {
            return redirect()->back()->with('error', 'Invalid report type.');
        }

        $fromDate = $request->get('from_date', now()->startOfYear()->toDateString());
        $toDate   = $request->get('to_date', now()->toDateString());

        $exportData = [];
        $filename = "{$report}_{$fromDate}_to_{$toDate}.pdf";

        switch ($report) {
            case 'trial-balance':
                $exportData = $this->getTrialBalanceData($fromDate, $toDate);
                break;
            case 'profit-loss':
                $exportData = $this->fetchPLAccounts($fromDate, $toDate);
                break;
            default:
                return redirect()->back()->with('warning', 'PDF export not yet implemented for this report. Use screen capture.');
        }

        if (empty($exportData)) {
            return redirect()->back()->with('error', 'No data to export.');
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('travel.reports.export-pdf', [
            'report'     => $report,
            'fromDate'   => $fromDate,
            'toDate'     => $toDate,
            'data'       => $exportData,
            'company'    => company(),
        ]);

        return $pdf->download($filename);
    }

    // ── Arrival Report ───────────────────────────────────────────────────────
    public function arrivalReport(Request $request)
    {
        $fromDate = $request->from_date ?? now()->toDateString();
        $toDate   = $request->to_date   ?? now()->toDateString();

        $this->passengers = DB::table('passengers as p')
            ->join('booking_groups as bg', 'bg.id', '=', 'p.booking_group_id')
            ->leftJoin('relations as r', 'r.id', '=', 'p.relation_id')
            ->leftJoin('packages as pkg', 'pkg.id', '=', 'bg.package_id')
            ->whereBetween('bg.departure_date', [$fromDate, $toDate])
            ->select([
                'p.first_name', 'p.family_name', 'p.passport_no', 'p.gender',
                'bg.group_name', 'bg.group_no', 'bg.departure_date',
                'pkg.name as package_name',
                'r.name as relation_name',
            ])
            ->orderBy('bg.departure_date')
            ->orderBy('bg.group_name')
            ->get();

        $this->fromDate = $fromDate;
        $this->toDate   = $toDate;
        return view('travel.reports.arrival', $this->data);
    }

    // ── Departure Report ─────────────────────────────────────────────────────
    public function departureReport(Request $request)
    {
        $fromDate = $request->from_date ?? now()->toDateString();
        $toDate   = $request->to_date   ?? now()->toDateString();

        $this->passengers = DB::table('passengers as p')
            ->join('booking_groups as bg', 'bg.id', '=', 'p.booking_group_id')
            ->leftJoin('relations as r', 'r.id', '=', 'p.relation_id')
            ->leftJoin('packages as pkg', 'pkg.id', '=', 'bg.package_id')
            ->whereBetween('bg.return_date', [$fromDate, $toDate])
            ->select([
                'p.first_name', 'p.family_name', 'p.passport_no', 'p.gender',
                'bg.group_name', 'bg.group_no', 'bg.return_date',
                'pkg.name as package_name',
                'r.name as relation_name',
            ])
            ->orderBy('bg.return_date')
            ->orderBy('bg.group_name')
            ->get();

        $this->fromDate = $fromDate;
        $this->toDate   = $toDate;
        return view('travel.reports.departure', $this->data);
    }

    // ── KSA Intimation Report ────────────────────────────────────────────────
    public function ksaIntimation(Request $request)
    {
        $fromDate = $request->from_date ?? now()->toDateString();
        $toDate   = $request->to_date   ?? now()->toDateString();

        $this->passengers = DB::table('passengers as p')
            ->join('booking_groups as bg', 'bg.id', '=', 'p.booking_group_id')
            ->leftJoin('relations as r', 'r.id', '=', 'p.relation_id')
            ->leftJoin('packages as pkg', 'pkg.id', '=', 'bg.package_id')
            ->whereBetween('bg.departure_date', [$fromDate, $toDate])
            ->select([
                'p.first_name', 'p.family_name', 'p.passport_no', 'p.gender',
                'p.birth_date', 'p.mofa_status',
                'bg.group_name', 'bg.group_no', 'bg.departure_date', 'bg.return_date',
                'pkg.name as package_name',
                'r.name as relation_name',
            ])
            ->orderBy('bg.departure_date')
            ->orderBy('p.family_name')
            ->get();

        $this->fromDate = $fromDate;
        $this->toDate   = $toDate;
        return view('travel.reports.ksa-intimation', $this->data);
    }

    // ── Makkah Hotel Check-In/Out Report ─────────────────────────────────────
    public function makkahHotel(Request $request)
    {
        $fromDate = $request->from_date ?? now()->startOfMonth()->toDateString();
        $toDate   = $request->to_date   ?? now()->endOfMonth()->toDateString();

        $this->allocations = DB::table('room_allocations as ra')
            ->join('hotel_rooms as hr', 'hr.id', '=', 'ra.hotel_room_id')
            ->join('hotels as h', 'h.id', '=', 'hr.hotel_id')
            ->join('passengers as p', 'p.id', '=', 'ra.passenger_id')
            ->join('booking_groups as bg', 'bg.id', '=', 'ra.booking_group_id')
            ->where(DB::raw('LOWER(h.city)'), 'like', '%makkah%')
            ->where(function ($q) use ($fromDate, $toDate) {
                $q->whereBetween('ra.check_in', [$fromDate, $toDate])
                  ->orWhereBetween('ra.check_out', [$fromDate, $toDate]);
            })
            ->select([
                'h.name as hotel_name', 'h.city', 'h.stars',
                'hr.room_number', 'hr.room_type', 'hr.capacity',
                'p.first_name', 'p.family_name', 'p.passport_no', 'p.gender',
                'ra.check_in', 'ra.check_out', 'ra.status as allocation_status',
                'bg.group_name', 'bg.group_no',
            ])
            ->orderBy('h.name')
            ->orderBy('ra.check_in')
            ->get();

        $this->fromDate = $fromDate;
        $this->toDate   = $toDate;
        $this->city     = 'Makkah';
        return view('travel.reports.makkah-hotel', $this->data);
    }

    // ── Madina Hotel Check-In/Out Report ─────────────────────────────────────
    public function madinaHotel(Request $request)
    {
        $fromDate = $request->from_date ?? now()->startOfMonth()->toDateString();
        $toDate   = $request->to_date   ?? now()->endOfMonth()->toDateString();

        $this->allocations = DB::table('room_allocations as ra')
            ->join('hotel_rooms as hr', 'hr.id', '=', 'ra.hotel_room_id')
            ->join('hotels as h', 'h.id', '=', 'hr.hotel_id')
            ->join('passengers as p', 'p.id', '=', 'ra.passenger_id')
            ->join('booking_groups as bg', 'bg.id', '=', 'ra.booking_group_id')
            ->where(function ($q) {
                $q->where(DB::raw('LOWER(h.city)'), 'like', '%madinah%')
                  ->orWhere(DB::raw('LOWER(h.city)'), 'like', '%medina%')
                  ->orWhere(DB::raw('LOWER(h.city)'), 'like', '%madina%');
            })
            ->where(function ($q) use ($fromDate, $toDate) {
                $q->whereBetween('ra.check_in', [$fromDate, $toDate])
                  ->orWhereBetween('ra.check_out', [$fromDate, $toDate]);
            })
            ->select([
                'h.name as hotel_name', 'h.city', 'h.stars',
                'hr.room_number', 'hr.room_type', 'hr.capacity',
                'p.first_name', 'p.family_name', 'p.passport_no', 'p.gender',
                'ra.check_in', 'ra.check_out', 'ra.status as allocation_status',
                'bg.group_name', 'bg.group_no',
            ])
            ->orderBy('h.name')
            ->orderBy('ra.check_in')
            ->get();

        $this->fromDate = $fromDate;
        $this->toDate   = $toDate;
        $this->city     = 'Madinah';
        return view('travel.reports.madina-hotel', $this->data);
    }

    // ── Travel Agent Comparison Report ────────────────────────────────────────
    public function agentComparison(Request $request)
    {
        $fromDate = $request->from_date ?? now()->startOfYear()->toDateString();
        $toDate   = $request->to_date   ?? now()->toDateString();

        $calculator = new \App\Services\Reports\UmrahPlCalculator();
        $plByGroup  = collect($calculator->calculateAll(company()->id ?? null))->keyBy('booking_group_id');

        $bookings = BookingGroup::with(['customer.clientDetails', 'passengers'])
            ->where('company_id', company()->id)
            ->whereBetween('created_at', [$fromDate . ' 00:00:00', $toDate . ' 23:59:59'])
            ->get();

        $grouped = $bookings->groupBy(fn($b) => $b->customer_id ?: 0);

        $agents = $grouped->map(function ($groupBookings, $customerId) use ($plByGroup) {
            $customer = $groupBookings->first()->customer;
            $customerName = $customer ? $customer->name : 'Walk-in / Direct Customer';
            $customerEmail = $customer ? $customer->email : '--';
            $companyName = $customer && $customer->clientDetails ? $customer->clientDetails->company_name : ($customer ? $customer->name : 'Direct');

            $totalBookings = $groupBookings->count();
            $cancelledBookings = $groupBookings->where('status', 'cancelled')->count();
            $confirmedBookings = $groupBookings->where('status', 'confirmed')->count();
            $completedBookings = $groupBookings->where('status', 'completed')->count();
            $pendingBookings   = $groupBookings->where('status', 'pending')->count();
            $totalPax = $groupBookings->sum(fn($b) => $b->passengers->count() > 0 ? $b->passengers->count() : ($b->total_pax ?: 0));

            $totalRevenue = 0.0;
            $totalCost    = 0.0;
            $totalMargin  = 0.0;

            foreach ($groupBookings as $b) {
                if (isset($plByGroup[$b->id])) {
                    $pl = $plByGroup[$b->id]['pl'];
                    $totalRevenue += $pl->revenue;
                    $totalCost    += $pl->cost;
                    $totalMargin  += $pl->margin;
                }
            }

            $cancellationRate = $totalBookings > 0 ? round(($cancelledBookings / $totalBookings) * 100, 1) : 0.0;
            $marginPercent    = $totalRevenue > 0 ? round(($totalMargin / $totalRevenue) * 100, 1) : 0.0;

            return (object) [
                'customer_id'       => $customerId,
                'name'              => $customerName,
                'company_name'      => $companyName,
                'email'             => $customerEmail,
                'total_bookings'    => $totalBookings,
                'cancelled_bookings'=> $cancelledBookings,
                'confirmed_bookings'=> $confirmedBookings,
                'completed_bookings'=> $completedBookings,
                'pending_bookings'  => $pendingBookings,
                'total_pax'         => $totalPax,
                'total_revenue'     => $totalRevenue,
                'total_cost'        => $totalCost,
                'gross_margin'      => $totalMargin,
                'margin_percent'    => $marginPercent,
                'cancellation_rate' => $cancellationRate,
            ];
        })->sortByDesc('gross_margin')->values();

        $this->agents         = $agents;
        $this->fromDate       = $fromDate;
        $this->toDate         = $toDate;
        $this->totalBookings  = $agents->sum('total_bookings');
        $this->totalRevenue   = $agents->sum('total_revenue');
        $this->totalCost      = $agents->sum('total_cost');
        $this->totalMargin    = $agents->sum('gross_margin');
        $this->totalPax       = $agents->sum('total_pax');

        return view('travel.reports.agent-comparison', $this->data);
    }

    // ── Employee Efficiency Report ───────────────────────────────────────────
    public function employeeEfficiency(Request $request)
    {
        $fromDate = $request->from_date ?? now()->startOfMonth()->toDateString();
        $toDate   = $request->to_date   ?? now()->toDateString();

        $employees = \App\Models\User::allEmployees(null, true, null, company()->id);

        $bookingsAgg = DB::table('booking_groups as bg')
            ->leftJoin('booking_charges as bc', 'bc.booking_group_id', '=', 'bg.id')
            ->where('bg.company_id', company()->id)
            ->whereBetween('bg.created_at', [$fromDate . ' 00:00:00', $toDate . ' 23:59:59'])
            ->groupBy('bg.added_by')
            ->select([
                'bg.added_by',
                DB::raw('COUNT(DISTINCT bg.id) as bookings_count'),
                DB::raw('COALESCE(SUM(bc.amount), 0) as total_revenue'),
            ])
            ->get()
            ->keyBy('added_by');

        $vouchersAgg = DB::table('vouchers')
            ->where('company_id', company()->id)
            ->where(function ($q) {
                $q->whereIn('status', ['issued', 'locked'])
                  ->orWhereNotNull('locked_at');
            })
            ->whereBetween('created_at', [$fromDate . ' 00:00:00', $toDate . ' 23:59:59'])
            ->groupBy(DB::raw('COALESCE(locked_by, added_by)'))
            ->select([
                DB::raw('COALESCE(locked_by, added_by) as emp_id'),
                DB::raw('COUNT(id) as vouchers_count'),
            ])
            ->get()
            ->keyBy('emp_id');

        $visasAgg = DB::table('visa_logs')
            ->where('company_id', company()->id)
            ->where('to_status', 'issued')
            ->whereBetween('created_at', [$fromDate . ' 00:00:00', $toDate . ' 23:59:59'])
            ->groupBy('changed_by')
            ->select([
                'changed_by',
                DB::raw('COUNT(id) as visas_issued_count'),
            ])
            ->get()
            ->keyBy('changed_by');

        $rows = $employees->map(function ($emp) use ($bookingsAgg, $vouchersAgg, $visasAgg) {
            $b = $bookingsAgg->get($emp->id);
            $v = $vouchersAgg->get($emp->id);
            $visa = $visasAgg->get($emp->id);

            $bookingsCount = $b ? (int) $b->bookings_count : 0;
            $revenue       = $b ? (float) $b->total_revenue : 0.0;
            $vouchersCount = $v ? (int) $v->vouchers_count : 0;
            $visasCount    = $visa ? (int) $visa->visas_issued_count : 0;

            return (object) [
                'employee'        => $emp,
                'name'            => $emp->name,
                'email'           => $emp->email,
                'department'      => $emp->employeeDetail?->department?->team_name ?? '--',
                'designation'     => $emp->employeeDetail?->designation?->name ?? '--',
                'bookings_count'  => $bookingsCount,
                'vouchers_count'  => $vouchersCount,
                'visas_count'     => $visasCount,
                'revenue'         => $revenue,
                'total_actions'   => $bookingsCount + $vouchersCount + $visasCount,
            ];
        })->sortByDesc('total_actions')->values();

        $this->rows           = $rows;
        $this->fromDate       = $fromDate;
        $this->toDate         = $toDate;
        $this->totalBookings  = $rows->sum('bookings_count');
        $this->totalVouchers  = $rows->sum('vouchers_count');
        $this->totalVisas     = $rows->sum('visas_count');
        $this->totalRevenue   = $rows->sum('revenue');

        return view('travel.reports.employee-efficiency', $this->data);
    }

    // ── Daily Cash Transaction Report ────────────────────────────────────────
    public function dailyCash(Request $request)
    {
        $fromDate  = $request->from_date ?? now()->startOfMonth()->toDateString();
        $toDate    = $request->to_date   ?? now()->toDateString();
        $accountId = $request->account_id;

        $openingBalance = (float) DB::table('cash_receipts')
            ->where('company_id', company()->id)
            ->whereDate('date', '<', $fromDate)
            ->when($accountId, fn($q) => $q->where('account_id', $accountId))
            ->sum('amount');

        $receipts = DB::table('cash_receipts as cr')
            ->leftJoin('chart_of_accounts as coa', 'coa.id', '=', 'cr.account_id')
            ->leftJoin('users as u', 'u.id', '=', 'cr.added_by')
            ->where('cr.company_id', company()->id)
            ->whereBetween('cr.date', [$fromDate, $toDate])
            ->when($accountId, fn($q) => $q->where('cr.account_id', $accountId))
            ->select([
                'cr.id', 'cr.date', 'cr.amount', 'cr.currency_code', 'cr.reference_no',
                'cr.received_from', 'cr.narration', 'cr.journal_voucher_id',
                'coa.name as account_name', 'coa.code as account_code',
                'u.name as added_by_name',
            ])
            ->orderBy('cr.date')
            ->orderBy('cr.id')
            ->get();

        $days = [];
        $runningBalance = $openingBalance;
        $groupedByDate = $receipts->groupBy('date');

        foreach ($groupedByDate as $date => $dayReceipts) {
            $dayTotal = (float) $dayReceipts->sum('amount');
            $startOfDayBal = $runningBalance;
            $runningBalance += $dayTotal;

            $days[] = (object) [
                'date'            => $date,
                'receipt_count'   => $dayReceipts->count(),
                'opening_balance' => $startOfDayBal,
                'daily_amount'    => $dayTotal,
                'closing_balance' => $runningBalance,
                'receipts'        => $dayReceipts,
            ];
        }

        $this->days           = $days;
        $this->receipts       = $receipts;
        $this->fromDate       = $fromDate;
        $this->toDate         = $toDate;
        $this->accountId      = $accountId;
        $this->accounts       = \App\Models\ChartOfAccount::where('company_id', company()->id)
            ->whereIn('type', ['asset', 'bank', 'cash'])
            ->orderBy('name')
            ->get();
        $this->openingBalance = $openingBalance;
        $this->totalReceipts  = (float) $receipts->sum('amount');
        $this->closingBalance = $runningBalance;
        $this->totalCount     = $receipts->count();

        return view('travel.reports.daily-cash', $this->data);
    }

    private function getTrialBalanceData(string $fromDate, string $toDate): array
    {
        return DB::table('journal_voucher_lines as jvl')
            ->join('journal_vouchers as jv', 'jv.id', '=', 'jvl.journal_voucher_id')
            ->join('chart_of_accounts as coa', 'coa.id', '=', 'jvl.account_id')
            ->where('jv.is_balanced', 1)
            ->whereBetween('jv.date', [$fromDate, $toDate])
            ->groupBy('coa.id', 'coa.code', 'coa.name', 'coa.type')
            ->select([
                'coa.code', 'coa.name', 'coa.type',
                DB::raw('ROUND(SUM(jvl.debit), 2) as total_debit'),
                DB::raw('ROUND(SUM(jvl.credit), 2) as total_credit'),
            ])
            ->orderBy('coa.code')
            ->get()
            ->toArray();
    }

    // ── Private helpers ───────────────────────────────────────────────────────
    private function fetchPLAccounts(string $fromDate, string $toDate): array
    {
        $query = fn($types) => DB::table('journal_voucher_lines as jvl')
            ->join('journal_vouchers as jv', 'jv.id', '=', 'jvl.journal_voucher_id')
            ->join('chart_of_accounts as coa', 'coa.id', '=', 'jvl.account_id')
            ->where('jv.is_balanced', 1)
            ->whereBetween('jv.date', [$fromDate, $toDate])
            ->whereIn('coa.type', $types)
            ->groupBy('coa.id', 'coa.code', 'coa.name', 'coa.type')
            ->select([
                'coa.id', 'coa.code', 'coa.name', 'coa.type',
                DB::raw('SUM(jvl.credit) - SUM(jvl.debit) as net'),
            ])
            ->orderBy('coa.code')
            ->get();

        // revenue, cogs, expenses, otherIncome
        return [
            $query(['income']),
            $query(['cogs']),
            $query(['expense']),
            $query(['other_income']),
        ];
    }
}
