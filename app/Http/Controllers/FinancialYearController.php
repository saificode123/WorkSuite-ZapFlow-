<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use App\Models\FinancialYear;
use Illuminate\Http\Request;
use App\DataTables\Accounts\FinancialYearDataTable;

class FinancialYearController extends AccountBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.financialYears';
        $this->middleware(function ($request, $next) {
            abort_403(!in_array('accounts', $this->user->modules));
            return $next($request);
        });
    }

    public function index(FinancialYearDataTable $dataTable)
    {
        $viewPermission = user()->permission('view_financial_year');
        abort_403(!in_array($viewPermission, ['all', 'added', 'owned', 'both']));
        return $dataTable->render('accounts.financial-years.index', $this->data);
    }

    public function create()
    {
        abort_403(!in_array(user()->permission('add_financial_year'), ['all', 'added']));
        $this->pageTitle = __('modules.accounts.addFinancialYear');
        return view('accounts.financial-years.create', $this->data);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:financial_years,name,null,id,company_id,' . company()->id,
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
        ]);
        $year = new FinancialYear();
        $year->company_id = company()->id;
        $year->name = $request->name;
        $year->start_date = $request->start_date;
        $year->end_date = $request->end_date;
        $year->save();
        return Reply::success(__('messages.recordSaved'));
    }

    public function edit($id)
    {
        abort_403(!in_array(user()->permission('edit_financial_year'), ['all', 'added']));
        $this->pageTitle = __('modules.accounts.editFinancialYear');
        $this->year = FinancialYear::findOrFail($id);
        return view('accounts.financial-years.edit', $this->data);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|unique:financial_years,name,' . $id . ',id,company_id,' . company()->id,
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
        ]);
        $year = FinancialYear::findOrFail($id);
        $year->name = $request->name;
        $year->start_date = $request->start_date;
        $year->end_date = $request->end_date;
        $year->save();
        return Reply::success(__('messages.updateSuccess'));
    }

    public function destroy($id)
    {
        $year = FinancialYear::findOrFail($id);
        if ($year->journalVouchers()->count() > 0) {
            return Reply::error(__('messages.financialYearHasVouchers'));
        }
        $year->delete();
        return Reply::success(__('messages.deleteSuccess'));
    }
}
