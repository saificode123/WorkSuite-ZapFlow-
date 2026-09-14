<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use App\Models\CashReceipt;
use App\Models\ChartOfAccount;
use App\Services\DoubleEntryService;
use App\DataTables\Travel\CashReceiptDataTable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CashReceiptController extends AccountBaseController
{
    public function __construct(private DoubleEntryService $de)
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.cashReceipts';

        $this->middleware(function ($request, $next) {
            abort_403(!in_array('accounts', $this->user->modules));
            return $next($request);
        });
    }

    public function index(CashReceiptDataTable $dataTable)
    {
        $viewPermission = user()->permission('view_cash_receipt');
        abort_403(!in_array($viewPermission, ['all', 'added', 'owned', 'both']));
        return $dataTable->render('travel.cash-receipts.index', $this->data);
    }

    public function create()
    {
        abort_403(!in_array(user()->permission('add_cash_receipt'), ['all', 'added']));

        $this->accounts = ChartOfAccount::whereIn('type', ['asset', 'bank'])
            ->where('company_id', company_id())
            ->orderBy('name')
            ->get();
        $this->view = 'travel.cash-receipts.ajax.create';

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }
        return view('travel.cash-receipts.create', $this->data);
    }

    public function store(Request $request)
    {
        abort_403(!in_array(user()->permission('add_cash_receipt'), ['all', 'added']));

        $validated = $request->validate([
            'account_id'    => 'required|exists:chart_of_accounts,id',
            'amount'        => 'required|numeric|min:0.01',
            'date'          => 'required|date',
            'received_from' => 'required|string|max:255',
            'reference_no'  => 'nullable|string|max:100',
            'narration'     => 'nullable|string|max:500',
        ]);

        return DB::transaction(function () use ($validated) {
            $jv = $this->de->post([
                'date'      => $validated['date'],
                'narration' => $validated['narration'] ?? 'Cash receipt from ' . $validated['received_from'],
                'lines'     => [
                    [
                        'account_id' => (int) $validated['account_id'],
                        'debit'      => (float) $validated['amount'],
                        'credit'     => 0,
                    ],
                    [
                        'account_id' => $this->getCashClearingAccount(),
                        'debit'      => 0,
                        'credit'     => (float) $validated['amount'],
                    ],
                ],
            ], user()->id);

            CashReceipt::create(array_merge($validated, [
                'company_id'        => company_id(),
                'currency_code'     => 'PKR',
                'journal_voucher_id' => $jv->id,
                'added_by'          => user()->id,
            ]));

            return Reply::successWithData(
                __('messages.recordSaved'),
                ['redirectUrl' => route('cash-receipts.index')]
            );
        });
    }

    public function destroy($id)
    {
        abort_403(!in_array(user()->permission('delete_cash_receipt'), ['all', 'added']));

        CashReceipt::findOrFail($id)->delete();
        return Reply::successWithData(__('messages.deleteSuccess'), ['redirectUrl' => route('cash-receipts.index')]);
    }

    private function getCashClearingAccount(): int
    {
        $account = ChartOfAccount::firstOrCreate(
            [
                'company_id' => company_id(),
                'type'       => 'liability',
                'name'       => 'Cash Clearing',
            ],
            [
                'level'             => 3,
                'is_system_account' => true,
                'currency_code'     => 'PKR',
            ]
        );
        return $account->id;
    }
}
