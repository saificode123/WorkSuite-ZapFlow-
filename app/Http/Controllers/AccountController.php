<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use App\Models\ChartOfAccount;
use App\Models\JournalVoucherLine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AccountController extends AccountBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.accounts';
        $this->middleware(function ($request, $next) {
            abort_403(!in_array('accounts', $this->user->modules));
            return $next($request);
        });
    }

    public function index()
    {
        return redirect()->route('accounts.trial_balance');
    }

    public function trialBalance(Request $request)
    {
        $this->pageTitle = __('modules.accounts.trialBalance');

        $accounts = ChartOfAccount::where('company_id', company()->id)
            ->with(['journalVoucherLines' => function ($q) use ($request) {
                if ($request->start_date) {
                    $q->whereHas('journalVoucher', function ($qv) use ($request) {
                        $qv->where('date', '>=', $request->start_date);
                    });
                }
                if ($request->end_date) {
                    $q->whereHas('journalVoucher', function ($qv) use ($request) {
                        $qv->where('date', '<=', $request->end_date);
                    });
                }
            }])
            ->get()
            ->map(function ($account) {
                $totalDebit = $account->journalVoucherLines->sum('debit');
                $totalCredit = $account->journalVoucherLines->sum('credit');
                $balance = $totalDebit - $totalCredit;
                $account->total_debit = $totalDebit;
                $account->total_credit = $totalCredit;
                $account->balance = $balance;
                return $account;
            });

        $this->accounts = $accounts;
        $this->totalDebit = $accounts->sum('total_debit');
        $this->totalCredit = $accounts->sum('total_credit');

        return view('accounts.trial-balance', $this->data);
    }

    public function ledger(Request $request)
    {
        $this->pageTitle = __('modules.accounts.ledger');
        $this->accounts = ChartOfAccount::where('company_id', company()->id)->get();

        if ($request->account_id) {
            $this->selectedAccount = ChartOfAccount::findOrFail($request->account_id);
            $query = JournalVoucherLine::with('journalVoucher')
                ->where('account_id', $request->account_id);

            if ($request->start_date) {
                $query->whereHas('journalVoucher', function ($q) use ($request) {
                    $q->where('date', '>=', $request->start_date);
                });
            }
            if ($request->end_date) {
                $query->whereHas('journalVoucher', function ($q) use ($request) {
                    $q->where('date', '<=', $request->end_date);
                });
            }

            $this->ledgerEntries = $query->orderBy('id')->get();
            $openingBalance = JournalVoucherLine::where('account_id', $request->account_id)
                ->whereHas('journalVoucher', function ($q) use ($request) {
                    if ($request->start_date) {
                        $q->where('date', '<', $request->start_date);
                    }
                })
                ->select(DB::raw('COALESCE(SUM(debit), 0) - COALESCE(SUM(credit), 0) as balance'))
                ->value('balance');

            $this->openingBalance = $openingBalance ?: 0;
        }

        return view('accounts.ledger', $this->data);
    }
}
