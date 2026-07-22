<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use App\Models\ChartOfAccount;
use Illuminate\Http\Request;
use App\DataTables\Accounts\ChartOfAccountDataTable;
use App\Http\Requests\ChartOfAccount\StoreRequest;
use App\Http\Requests\ChartOfAccount\UpdateRequest;

class ChartOfAccountController extends AccountBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.chartOfAccounts';
        $this->middleware(function ($request, $next) {
            abort_403(!in_array('accounts', $this->user->modules));
            return $next($request);
        });
    }

    public function index(ChartOfAccountDataTable $dataTable)
    {
        $viewPermission = user()->permission('view_chart_of_account');
        abort_403(!in_array($viewPermission, ['all', 'added', 'owned', 'both']));
        return $dataTable->render('accounts.chart-of-accounts.index', $this->data);
    }

    public function create()
    {
        $this->addPermission = user()->permission('add_chart_of_account');
        abort_403(!in_array($this->addPermission, ['all', 'added']));
        $this->pageTitle = __('modules.accounts.addAccount');
        $this->parents = ChartOfAccount::where('company_id', company()->id)->get()->groupBy('type');
        $this->types = ['asset', 'liability', 'equity', 'income', 'expense'];
        return view('accounts.chart-of-accounts.create', $this->data);
    }

    public function store(StoreRequest $request)
    {
        $account = new ChartOfAccount();
        $account->company_id = company()->id;
        $account->name = $request->name;
        $account->code = $request->code;
        $account->type = $request->type;
        $account->parent_id = $request->parent_id;
        $account->level = $request->parent_id ? (ChartOfAccount::find($request->parent_id)->level + 1) : 1;
        $account->is_bank_account = $request->is_bank_account ?? false;
        $account->save();
        return Reply::successWithData(__('messages.recordSaved'), ['account_id' => $account->id]);
    }

    public function edit($id)
    {
        $this->editPermission = user()->permission('edit_chart_of_account');
        $this->account = ChartOfAccount::findOrFail($id);
        abort_403(!in_array($this->editPermission, ['all', 'added']));
        $this->pageTitle = __('modules.accounts.editAccount');
        $this->parents = ChartOfAccount::where('company_id', company()->id)->where('id', '!=', $id)->get()->groupBy('type');
        $this->types = ['asset', 'liability', 'equity', 'income', 'expense'];
        return view('accounts.chart-of-accounts.edit', $this->data);
    }

    public function update(UpdateRequest $request, $id)
    {
        $account = ChartOfAccount::findOrFail($id);
        $account->name = $request->name;
        $account->code = $request->code;
        $account->type = $request->type;
        $account->parent_id = $request->parent_id;
        if ($request->parent_id) {
            $account->level = ChartOfAccount::find($request->parent_id)->level + 1;
        } else {
            $account->level = 1;
        }
        $account->is_bank_account = $request->is_bank_account ?? false;
        $account->save();
        return Reply::success(__('messages.updateSuccess'));
    }

    public function destroy($id)
    {
        $account = ChartOfAccount::findOrFail($id);
        if ($account->children()->count() > 0) {
            return Reply::error(__('messages.accountHasChildren'));
        }
        if ($account->journalVoucherLines()->count() > 0) {
            return Reply::error(__('messages.accountHasTransactions'));
        }
        $account->delete();
        return Reply::success(__('messages.deleteSuccess'));
    }

    public function tree()
    {
        $accounts = ChartOfAccount::where('company_id', company()->id)
            ->with('children')
            ->whereNull('parent_id')
            ->get();
        return response()->json($accounts);
    }
}
