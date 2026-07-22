<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use App\Models\JournalVoucher;
use App\Models\ChartOfAccount;
use App\Models\FinancialYear;
use App\Models\JournalVoucherLine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\DataTables\Accounts\JournalVoucherDataTable;
use App\Http\Requests\JournalVoucher\StoreRequest;
use App\Http\Requests\JournalVoucher\UpdateRequest;

class JournalVoucherController extends AccountBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.journalVouchers';
        $this->middleware(function ($request, $next) {
            abort_403(!in_array('accounts', $this->user->modules));
            return $next($request);
        });
    }

    public function index(JournalVoucherDataTable $dataTable)
    {
        $viewPermission = user()->permission('view_journal_voucher');
        abort_403(!in_array($viewPermission, ['all', 'added', 'owned', 'both']));
        return $dataTable->render('accounts.journal-vouchers.index', $this->data);
    }

    public function create()
    {
        abort_403(!in_array(user()->permission('add_journal_voucher'), ['all', 'added']));
        $this->pageTitle = __('modules.accounts.addJournalVoucher');
        $this->accounts = ChartOfAccount::where('company_id', company()->id)->get();
        $this->financialYears = FinancialYear::where('company_id', company()->id)->where('is_closed', false)->get();
        $this->voucherNumber = 'JV-' . date('Ymd') . '-' . str_pad(JournalVoucher::whereDate('created_at', today())->count() + 1, 3, '0', STR_PAD_LEFT);
        return view('accounts.journal-vouchers.create', $this->data);
    }

    public function store(StoreRequest $request)
    {
        $totalDebit = 0;
        $totalCredit = 0;
        foreach ($request->lines as $line) {
            $totalDebit += $line['debit'] ?? 0;
            $totalCredit += $line['credit'] ?? 0;
        }

        if (abs($totalDebit - $totalCredit) > 0.01) {
            return Reply::error(__('messages.voucherNotBalanced'));
        }

        $voucher = DB::transaction(function () use ($request) {
            $v = JournalVoucher::create([
                'company_id' => company()->id,
                'financial_year_id' => $request->financial_year_id,
                'voucher_number' => $request->voucher_number,
                'date' => $request->date,
                'created_by' => user()->id,
                'narration' => $request->narration,
                'is_balanced' => true,
            ]);

            foreach ($request->lines as $line) {
                JournalVoucherLine::create([
                    'journal_voucher_id' => $v->id,
                    'account_id' => $line['account_id'],
                    'debit' => $line['debit'] ?? 0,
                    'credit' => $line['credit'] ?? 0,
                    'description' => $line['description'] ?? null,
                ]);
            }

            return $v;
        });

        return Reply::successWithData(__('messages.recordSaved'), ['voucher_id' => $voucher->id]);
    }

    public function show($id)
    {
        $this->voucher = JournalVoucher::with('lines.account', 'financialYear', 'createdBy')->findOrFail($id);
        $this->pageTitle = __('modules.accounts.viewJournalVoucher');
        return view('accounts.journal-vouchers.show', $this->data);
    }

    public function edit($id)
    {
        abort_403(!in_array(user()->permission('edit_journal_voucher'), ['all', 'added']));
        $this->pageTitle = __('modules.accounts.editJournalVoucher');
        $this->voucher = JournalVoucher::with('lines')->findOrFail($id);
        $this->accounts = ChartOfAccount::where('company_id', company()->id)->get();
        $this->financialYears = FinancialYear::where('company_id', company()->id)->get();
        return view('accounts.journal-vouchers.edit', $this->data);
    }

    public function update(UpdateRequest $request, $id)
    {
        $voucher = JournalVoucher::findOrFail($id);

        $totalDebit = 0;
        $totalCredit = 0;
        foreach ($request->lines as $line) {
            $totalDebit += $line['debit'] ?? 0;
            $totalCredit += $line['credit'] ?? 0;
        }

        if (abs($totalDebit - $totalCredit) > 0.01) {
            return Reply::error(__('messages.voucherNotBalanced'));
        }

        DB::transaction(function () use ($request, $voucher) {
            $voucher->update([
                'financial_year_id' => $request->financial_year_id,
                'date' => $request->date,
                'narration' => $request->narration,
            ]);

            $voucher->lines()->delete();
            foreach ($request->lines as $line) {
                JournalVoucherLine::create([
                    'journal_voucher_id' => $voucher->id,
                    'account_id' => $line['account_id'],
                    'debit' => $line['debit'] ?? 0,
                    'credit' => $line['credit'] ?? 0,
                    'description' => $line['description'] ?? null,
                ]);
            }
        });

        return Reply::success(__('messages.updateSuccess'));
    }

    public function destroy($id)
    {
        $voucher = JournalVoucher::findOrFail($id);
        $voucher->lines()->delete();
        $voucher->delete();
        return Reply::success(__('messages.deleteSuccess'));
    }

    public function print($id)
    {
        $this->voucher = JournalVoucher::with('lines.account', 'financialYear', 'createdBy')->findOrFail($id);
        return view('accounts.journal-vouchers.print', $this->data);
    }
}
