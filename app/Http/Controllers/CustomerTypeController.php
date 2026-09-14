<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use Illuminate\Http\Request;
use App\Models\CustomerType;
use App\DataTables\Travel\CustomerTypeDataTable;

class CustomerTypeController extends AccountBaseController
{

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.customerTypes';

        $this->middleware(function ($request, $next) {
            abort_403(!in_array('customer_types', $this->user->modules));
            return $next($request);
        });
    }

    public function index(CustomerTypeDataTable $dataTable)
    {
        $viewPermission = user()->permission('view_customer_type');
        abort_403(!in_array($viewPermission, ['all', 'added', 'owned', 'both']));

        return $dataTable->render('travel.customer-types.index', $this->data);
    }

    public function create()
    {
        $this->addPermission = user()->permission('add_customer_type');
        abort_403(!in_array($this->addPermission, ['all', 'added']));

        $this->view = 'travel.customer-types.ajax.create';

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return view('travel.customer-types.create', $this->data);
    }

    public function store(Request $request)
    {
        $this->addPermission = user()->permission('add_customer_type');
        abort_403(!in_array($this->addPermission, ['all', 'added']));

        $request->validate([
            'name' => 'required|max:255',
        ]);

        $customerType = new CustomerType();
        $customerType->name = $request->name;
        $customerType->description = $request->description;
        $customerType->config_json = $request->config_json;
        $customerType->save();

        $redirectUrl = urldecode($request->redirect_url);

        if ($redirectUrl == '') {
            $redirectUrl = route('customer-types.index');
        }

        return Reply::successWithData(__('messages.recordSaved'), ['redirectUrl' => $redirectUrl]);
    }

    public function edit($id)
    {
        $this->editPermission = user()->permission('edit_customer_type');
        abort_403(!in_array($this->editPermission, ['all', 'added']));

        $this->customerType = CustomerType::findOrFail($id);
        $this->view = 'travel.customer-types.ajax.edit';

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return view('travel.customer-types.create', $this->data);
    }

    public function update(Request $request, $id)
    {
        $this->editPermission = user()->permission('edit_customer_type');
        abort_403(!in_array($this->editPermission, ['all', 'added']));

        $request->validate([
            'name' => 'required|max:255',
        ]);

        $customerType = CustomerType::findOrFail($id);
        $customerType->name = $request->name;
        $customerType->description = $request->description;
        $customerType->config_json = $request->config_json;
        $customerType->save();

        $redirectUrl = route('customer-types.index');

        return Reply::successWithData(__('messages.updateSuccess'), ['redirectUrl' => $redirectUrl]);
    }

    public function destroy($id)
    {
        $this->deletePermission = user()->permission('delete_customer_type');
        abort_403(!in_array($this->deletePermission, ['all', 'added']));

        CustomerType::destroy($id);

        return Reply::successWithData(__('messages.deleteSuccess'), ['redirectUrl' => route('customer-types.index')]);
    }

}
