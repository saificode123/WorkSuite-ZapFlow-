<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use App\Models\AccountOpening;
use App\Models\ChartOfAccount;
use App\Models\FinancialYear;
use Illuminate\Http\Request;
use App\DataTables\Accounts\AccountOpeningDataTable;

class AccountOpeningController extends AccountBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.accountOpenings';
        $this->middleware(function ($request, $next) {
            abort_403(!in_array('accounts', $this->user->modules));
            return $next($request);
        });
    }

    public function index(AccountOpeningDataTable $dataTable)
    {
        abort_403(!in_array(user()->permission('view_account_opening'), ['all', 'added', 'owned', 'both']));
        return $dataTable->render('accounts.account-openings.index', $this->data);
    }

    public function create()
    {
        abort_403(!in_array(user()->permission('add_account_opening'), ['all', 'added']));
        $this->pageTitle = __('modules.accounts.addAccountOpening');
        $this->financialYears = FinancialYear::where('company_id', company()->id)->get();
        $this->accounts = ChartOfAccount::where('company_id', company()->id)->where('level', '>', 1)->get();
        return view('accounts.account-openings.create', $this->data);
    }

    public function store(Request $request)
    {
        abort_403(!in_array(user()->permission('add_account_opening'), ['all', 'added']));

        $request->validate([
            'account_id' => 'required|exists:chart_of_accounts,id',
            'financial_year_id' => 'required|exists:financial_years,id',
            'opening_balance' => 'required|numeric',
        ]);

        $exists = AccountOpening::where('company_id', company()->id)
            ->where('account_id', $request->account_id)
            ->where('financial_year_id', $request->financial_year_id)
            ->first();

        if ($exists) {
            return Reply::error(__('messages.accountOpeningExists'));
        }

        $opening = new AccountOpening();
        $opening->company_id = company()->id;
        $opening->account_id = $request->account_id;
        $opening->financial_year_id = $request->financial_year_id;
        $opening->opening_balance = $request->opening_balance;
        $opening->save();
        return Reply::success(__('messages.recordSaved'));
    }

    public function edit($id)
    {
        abort_403(!in_array(user()->permission('edit_account_opening'), ['all', 'added']));
        $this->pageTitle = __('modules.accounts.editAccountOpening');
        $this->opening = AccountOpening::findOrFail($id);
        $this->financialYears = FinancialYear::where('company_id', company()->id)->get();
        $this->accounts = ChartOfAccount::where('company_id', company()->id)->where('level', '>', 1)->get();
        return view('accounts.account-openings.edit', $this->data);
    }

    public function update(Request $request, $id)
    {
        abort_403(!in_array(user()->permission('edit_account_opening'), ['all', 'added']));

        $request->validate([
            'account_id' => 'required|exists:chart_of_accounts,id',
            'financial_year_id' => 'required|exists:financial_years,id',
            'opening_balance' => 'required|numeric',
        ]);
        $opening = AccountOpening::findOrFail($id);
        $opening->account_id = $request->account_id;
        $opening->financial_year_id = $request->financial_year_id;
        $opening->opening_balance = $request->opening_balance;
        $opening->save();
        return Reply::success(__('messages.updateSuccess'));
    }

    public function destroy($id)
    {
        abort_403(!in_array(user()->permission('delete_account_opening'), ['all', 'added']));

        AccountOpening::findOrFail($id)->delete();
        return Reply::success(__('messages.deleteSuccess'));
    }
}
