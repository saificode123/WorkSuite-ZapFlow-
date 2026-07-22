<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use App\Models\InsuranceSale;
use Illuminate\Http\Request;

class InsuranceSaleController extends AccountBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app\menu\insuranceSales';
    }

    public function index()
    {
        $this->sales = InsuranceSale::with(['policy', 'bookingGroup', 'passenger'])
            ->latest()
            ->paginate(25);
        return view('travel.insurance.sales-index', $this->data);
    }

    public function store(Request $request)
    {
        $request->validate([
            'insurance_policy_id' => 'required|exists:insurance_policies,id',
            'passenger_id'        => 'required|exists:passengers,id',
            'booking_group_id'    => 'nullable|exists:booking_groups,id',
            'premium_amount'      => 'required|numeric|min:0',
            'start_date'          => 'nullable|date',
            'end_date'            => 'nullable|date|after_or_equal:start_date',
        ]);

        InsuranceSale::create(array_merge($request->only([
            'insurance_policy_id', 'passenger_id', 'booking_group_id',
            'premium_amount', 'policy_no', 'start_date', 'end_date', 'status',
        ]), ['issued_by' => user()->id]));

        return Reply::successWithData(
            __('messages.recordSaved'),
            ['redirectUrl' => route('insurance-sales.index')]
        );
    }

    public function destroy($id)
    {
        InsuranceSale::findOrFail($id)->delete();
        return Reply::successWithData(
            __('messages.deleteSuccess'),
            ['redirectUrl' => route('insurance-sales.index')]
        );
    }
}
