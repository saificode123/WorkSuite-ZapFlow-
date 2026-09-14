<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use App\Models\ExchangeRate;
use Illuminate\Http\Request;
use App\DataTables\Accounts\ExchangeRateDataTable;

class ExchangeRateController extends AccountBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.exchangeRates';
        $this->middleware(function ($request, $next) {
            abort_403(!in_array('accounts', $this->user->modules));
            return $next($request);
        });
    }

    public function index(ExchangeRateDataTable $dataTable)
    {
        abort_403(!in_array(user()->permission('view_exchange_rate'), ['all', 'added', 'owned', 'both']));
        return $dataTable->render('accounts.exchange-rates.index', $this->data);
    }

    public function create()
    {
        abort_403(!in_array(user()->permission('add_exchange_rate'), ['all', 'added']));
        $this->pageTitle = __('modules.accounts.addExchangeRate');
        return view('accounts.exchange-rates.create', $this->data);
    }

    public function store(Request $request)
    {
        abort_403(!in_array(user()->permission('add_exchange_rate'), ['all', 'added']));

        $request->validate([
            'currency_code' => 'required|max:10',
            'rate_to_base' => 'required|numeric',
            'effective_date' => 'required|date',
        ]);
        $rate = new ExchangeRate();
        $rate->company_id = company()->id;
        $rate->currency_code = strtoupper($request->currency_code);
        $rate->rate_to_base = $request->rate_to_base;
        $rate->effective_date = $request->effective_date;
        $rate->save();
        return Reply::success(__('messages.recordSaved'));
    }

    public function edit($id)
    {
        abort_403(!in_array(user()->permission('edit_exchange_rate'), ['all', 'added']));
        $this->pageTitle = __('modules.accounts.editExchangeRate');
        $this->rate = ExchangeRate::findOrFail($id);
        return view('accounts.exchange-rates.edit', $this->data);
    }

    public function update(Request $request, $id)
    {
        abort_403(!in_array(user()->permission('edit_exchange_rate'), ['all', 'added']));

        $request->validate([
            'currency_code' => 'required|max:10',
            'rate_to_base' => 'required|numeric',
            'effective_date' => 'required|date',
        ]);
        $rate = ExchangeRate::findOrFail($id);
        $rate->currency_code = strtoupper($request->currency_code);
        $rate->rate_to_base = $request->rate_to_base;
        $rate->effective_date = $request->effective_date;
        $rate->save();
        return Reply::success(__('messages.updateSuccess'));
    }

    public function destroy($id)
    {
        abort_403(!in_array(user()->permission('delete_exchange_rate'), ['all', 'added']));

        ExchangeRate::findOrFail($id)->delete();
        return Reply::success(__('messages.deleteSuccess'));
    }
}
