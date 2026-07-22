<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use Illuminate\Http\Request;
use App\Models\VisaCompany;
use App\DataTables\Travel\VisaCompanyDataTable;

class VisaCompanyController extends AccountBaseController
{

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.visaCompanies';

        $this->middleware(function ($request, $next) {
            abort_403(!in_array('visa_company', $this->user->modules));
            return $next($request);
        });
    }

    public function index(VisaCompanyDataTable $dataTable)
    {
        $viewPermission = user()->permission('view_visa_company');
        abort_403(!in_array($viewPermission, ['all', 'added', 'owned', 'both']));

        return $dataTable->render('travel.visa-companies.index', $this->data);
    }

    public function create()
    {
        $this->addPermission = user()->permission('add_visa_company');
        abort_403(!in_array($this->addPermission, ['all', 'added']));

        $this->view = 'travel.visa-companies.ajax.create';

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return view('travel.visa-companies.create', $this->data);
    }

    public function store(Request $request)
    {
        $this->addPermission = user()->permission('add_visa_company');
        abort_403(!in_array($this->addPermission, ['all', 'added']));

        $request->validate([
            'name' => 'required|max:255',
            'country' => 'required|max:255',
            'fee' => 'nullable|numeric',
            'processing_days' => 'nullable|integer',
        ]);

        $visaCompany = new VisaCompany();
        $visaCompany->name = $request->name;
        $visaCompany->country = $request->country;
        $visaCompany->fee = $request->fee;
        $visaCompany->processing_days = $request->processing_days;
        $visaCompany->save();

        $redirectUrl = urldecode($request->redirect_url);

        if ($redirectUrl == '') {
            $redirectUrl = route('visa-companies.index');
        }

        return Reply::successWithData(__('messages.recordSaved'), ['redirectUrl' => $redirectUrl]);
    }

    public function edit($id)
    {
        $this->editPermission = user()->permission('edit_visa_company');
        abort_403(!in_array($this->editPermission, ['all', 'added']));

        $this->visaCompany = VisaCompany::findOrFail($id);
        $this->view = 'travel.visa-companies.ajax.edit';

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return view('travel.visa-companies.create', $this->data);
    }

    public function update(Request $request, $id)
    {
        $this->editPermission = user()->permission('edit_visa_company');
        abort_403(!in_array($this->editPermission, ['all', 'added']));

        $request->validate([
            'name' => 'required|max:255',
            'country' => 'required|max:255',
            'fee' => 'nullable|numeric',
            'processing_days' => 'nullable|integer',
        ]);

        $visaCompany = VisaCompany::findOrFail($id);
        $visaCompany->name = $request->name;
        $visaCompany->country = $request->country;
        $visaCompany->fee = $request->fee;
        $visaCompany->processing_days = $request->processing_days;
        $visaCompany->save();

        return Reply::successWithData(__('messages.updateSuccess'), ['redirectUrl' => route('visa-companies.index')]);
    }

    public function destroy($id)
    {
        $this->deletePermission = user()->permission('delete_visa_company');
        abort_403(!in_array($this->deletePermission, ['all', 'added']));

        VisaCompany::destroy($id);

        return Reply::successWithData(__('messages.deleteSuccess'), ['redirectUrl' => route('visa-companies.index')]);
    }

}
