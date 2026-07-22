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
            abort_403(!in_array('accounting', $this->user->modules));
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
            ->where('jv.status', 'posted')
            ->whereBetween('jv.voucher_date', [$fromDate, $toDate])
            ->groupBy('coa.id', 'coa.code', 'coa.name', 'coa.type', 'coa.normal_balance')
            ->select([
                'coa.id', 'coa.code', 'coa.name', 'coa.type', 'coa.normal_balance',
                DB::raw('SUM(jvl.debit) as total_debit'),
                DB::raw('SUM(jvl.credit) as total_credit'),
            ])
            ->orderBy('coa.code')
            ->get();

        $this->balances   = $balances;
        $this->fromDate   = $fromDate;
        $this->toDate     = $toDate;
        $this->totalDebit = $balances->sum('total_debit');
        $this->totalCredit= $balances->sum('total_credit');

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
            ->where('jv.status', 'posted')
            ->whereYear('jv.voucher_date', $year)
            ->whereIn('coa.type', ['income', 'expense', 'cogs'])
            ->groupBy(DB::raw('MONTH(jv.voucher_date)'), 'coa.type')
            ->select([
                DB::raw('MONTH(jv.voucher_date) as month'),
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
        // P&L grouped by booking group
        $this->bookings = BookingGroup::with([
            'package', 'passengers',
            'travelPayments',
        ])->orderByDesc('departure_date')->paginate(20);

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

    private function getTrialBalanceData(string $fromDate, string $toDate): array
    {
        return DB::table('journal_voucher_lines as jvl')
            ->join('journal_vouchers as jv', 'jv.id', '=', 'jvl.journal_voucher_id')
            ->join('chart_of_accounts as coa', 'coa.id', '=', 'jvl.account_id')
            ->where('jv.status', 'posted')
            ->whereBetween('jv.voucher_date', [$fromDate, $toDate])
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
            ->where('jv.status', 'posted')
            ->whereBetween('jv.voucher_date', [$fromDate, $toDate])
            ->whereIn('coa.type', $types)
            ->groupBy('coa.id', 'coa.code', 'coa.name', 'coa.type')
            ->select([
                'coa.id', 'coa.code', 'coa.name', 'coa.type',
                DB::raw('SUM(jvl.credit) - SUM(jvl.debit) as net'),
            ])
            ->orderBy('coa.code')
            ->get();

        return [
            $query(['income']),
            $query(['cogs']),
            $query(['expense']),
            $query(['other_income']),
        ];
    }
}
