<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use App\Models\InsurancePolicy;
use App\Models\InsuranceSale;
use App\Models\BookingGroup;
use Illuminate\Http\Request;

class InsurancePolicyController extends AccountBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.insurancePolicies';
    }

    public function index()
    {
        $this->policies = InsurancePolicy::orderBy('name')->paginate(25);
        return view('travel.insurance.policies-index', $this->data);
    }

    public function create()
    {
        $this->view = 'travel.insurance.ajax.policy-create';
        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }
        return view('travel.insurance.policies-index', $this->data);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'         => 'required|string|max:255',
            'provider'     => 'nullable|string|max:255',
            'coverage_type'=> 'nullable|string|max:100',
            'premium_pax'  => 'nullable|numeric|min:0',
        ]);

        InsurancePolicy::create($request->only(['name','provider','coverage_type','premium_pax','description','is_active']));
        return Reply::successWithData(__('messages.recordSaved'), ['redirectUrl' => route('insurance-policies.index')]);
    }

    public function edit($id)
    {
        $this->policy = InsurancePolicy::findOrFail($id);
        $this->view = 'travel.insurance.ajax.policy-edit';
        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }
        return view('travel.insurance.policies-index', $this->data);
    }

    public function update(Request $request, $id)
    {
        InsurancePolicy::findOrFail($id)->update($request->only(['name','provider','coverage_type','premium_pax','description','is_active']));
        return Reply::successWithData(__('messages.updateSuccess'), ['redirectUrl' => route('insurance-policies.index')]);
    }

    public function destroy($id)
    {
        InsurancePolicy::findOrFail($id)->delete();
        return Reply::successWithData(__('messages.deleteSuccess'), ['redirectUrl' => route('insurance-policies.index')]);
    }
}
