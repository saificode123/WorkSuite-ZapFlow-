<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use App\Models\InsuranceSale;
use App\DataTables\Travel\InsuranceSaleDataTable;
use Illuminate\Http\Request;

class InsuranceSaleController extends AccountBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.insuranceSales';

        $this->middleware(function ($request, $next) {
            abort_403(!in_array('bookings', $this->user->modules));
            return $next($request);
        });
    }

    public function index(InsuranceSaleDataTable $dataTable)
    {
        $viewPermission = user()->permission('view_insurance_sale');
        abort_403(!in_array($viewPermission, ['all', 'added', 'owned', 'both']));
        return $dataTable->render('travel.insurance.sales-index', $this->data);
    }

    public function store(Request $request)
    {
        abort_403(!in_array(user()->permission('add_insurance_sale'), ['all', 'added']));
        $request->validate([
            'policy_id'          => 'required|exists:insurance_policies,id',
            'passenger_id'       => 'required|exists:passengers,id',
            'booking_group_id'   => 'nullable|exists:booking_groups,id',
            'amount'             => 'required|numeric|min:0',
            'certificate_number' => 'nullable|string|max:100',
            'currency_code'      => 'nullable|string|max:10',
        ]);

        InsuranceSale::create(array_merge($request->only([
            'policy_id', 'passenger_id', 'booking_group_id',
            'amount', 'certificate_number', 'currency_code', 'status',
        ]), [
            'company_id' => company()->id,
            'added_by'   => user()->id,
        ]));

        return Reply::successWithData(
            __('messages.recordSaved'),
            ['redirectUrl' => route('insurance-sales.index')]
        );
    }

    public function destroy($id)
    {
        abort_403(!in_array(user()->permission('delete_insurance_sale'), ['all', 'added']));
        InsuranceSale::findOrFail($id)->delete();
        return Reply::successWithData(
            __('messages.deleteSuccess'),
            ['redirectUrl' => route('insurance-sales.index')]
        );
    }
}
