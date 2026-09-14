<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use App\Models\InsurancePolicy;
use App\Models\InsuranceSale;
use App\Models\BookingGroup;
use App\DataTables\Travel\InsurancePolicyDataTable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InsurancePolicyController extends AccountBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.insurancePolicies';

        $this->middleware(function ($request, $next) {
            abort_403(!in_array('bookings', $this->user->modules));
            return $next($request);
        });
    }

    public function index(InsurancePolicyDataTable $dataTable)
    {
        $viewPermission = user()->permission('view_insurance_policy');
        abort_403(!in_array($viewPermission, ['all', 'added', 'owned', 'both']));
        return $dataTable->render('travel.insurance.policies-index', $this->data);
    }

    public function create()
    {
        abort_403(!in_array(user()->permission('add_insurance_policy'), ['all', 'added']));
        $this->view = 'travel.insurance.ajax.policy-create';
        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }
        return view('travel.insurance.policies-index', $this->data);
    }

    public function store(Request $request)
    {
        abort_403(!in_array(user()->permission('add_insurance_policy'), ['all', 'added']));
        $request->validate([
            'provider_name'    => 'required|string|max:255',
            'policy_type'      => 'nullable|string|max:100',
            'policy_number'    => 'nullable|string|max:100',
            'rate'             => 'nullable|numeric|min:0',
            'currency_code'    => 'nullable|string|max:10',
            'coverage_summary' => 'nullable|string|max:2000',
            'valid_from'       => 'nullable|date',
            'valid_to'         => 'nullable|date|after_or_equal:valid_from',
        ]);

        $data = $request->only(['provider_name','policy_type','policy_number','rate','currency_code','coverage_summary','valid_from','valid_to','is_active']);
        $data['company_id'] = company()->id;
        $data['added_by']   = user()->id;

        InsurancePolicy::create($data);
        return Reply::successWithData(__('messages.recordSaved'), ['redirectUrl' => route('insurance-policies.index')]);
    }

    public function edit($id)
    {
        abort_403(!in_array(user()->permission('edit_insurance_policy'), ['all', 'added']));
        $this->policy = InsurancePolicy::findOrFail($id);
        $this->view = 'travel.insurance.ajax.policy-edit';
        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }
        return view('travel.insurance.policies-index', $this->data);
    }

    public function update(Request $request, $id)
    {
        abort_403(!in_array(user()->permission('edit_insurance_policy'), ['all', 'added']));
        $request->validate([
            'provider_name'    => 'required|string|max:255',
            'policy_type'      => 'nullable|string|max:100',
            'policy_number'    => 'nullable|string|max:100',
            'rate'             => 'nullable|numeric|min:0',
            'coverage_summary' => 'nullable|string|max:2000',
        ]);

        InsurancePolicy::findOrFail($id)->update(
            $request->only(['provider_name','policy_type','policy_number','rate','currency_code','coverage_summary','valid_from','valid_to','is_active'])
        );
        return Reply::successWithData(__('messages.updateSuccess'), ['redirectUrl' => route('insurance-policies.index')]);
    }

    public function destroy($id)
    {
        abort_403(!in_array(user()->permission('delete_insurance_policy'), ['all', 'added']));
        InsurancePolicy::findOrFail($id)->delete();
        return Reply::successWithData(__('messages.deleteSuccess'), ['redirectUrl' => route('insurance-policies.index')]);
    }
}
