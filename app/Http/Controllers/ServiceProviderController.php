<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use Illuminate\Http\Request;
use App\Models\ServiceProvider;
use App\DataTables\Travel\ServiceProviderDataTable;

class ServiceProviderController extends AccountBaseController
{

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.serviceProviders';

        $this->middleware(function ($request, $next) {
            abort_403(!in_array('service_provider', $this->user->modules));
            return $next($request);
        });
    }

    public function index(ServiceProviderDataTable $dataTable)
    {
        $viewPermission = user()->permission('view_service_provider');
        abort_403(!in_array($viewPermission, ['all', 'added', 'owned', 'both']));

        return $dataTable->render('travel.service-providers.index', $this->data);
    }

    public function create()
    {
        $this->addPermission = user()->permission('add_service_provider');
        abort_403(!in_array($this->addPermission, ['all', 'added']));

        $this->view = 'travel.service-providers.ajax.create';

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return view('travel.service-providers.create', $this->data);
    }

    public function store(Request $request)
    {
        $this->addPermission = user()->permission('add_service_provider');
        abort_403(!in_array($this->addPermission, ['all', 'added']));

        $request->validate([
            'name' => 'required|max:255',
            'type' => 'required|in:hotel,transporter,airline,visa,iata,other',
            'email' => 'nullable|email|max:255',
        ]);

        $serviceProvider = new ServiceProvider();
        $serviceProvider->name = $request->name;
        $serviceProvider->type = $request->type;
        $serviceProvider->contact_person = $request->contact_person;
        $serviceProvider->email = $request->email;
        $serviceProvider->phone = $request->phone;
        $serviceProvider->address = $request->address;
        $serviceProvider->status = $request->status ?? 'active';
        $serviceProvider->save();

        $redirectUrl = urldecode($request->redirect_url);

        if ($redirectUrl == '') {
            $redirectUrl = route('service-providers.index');
        }

        return Reply::successWithData(__('messages.recordSaved'), ['redirectUrl' => $redirectUrl]);
    }

    public function edit($id)
    {
        $this->editPermission = user()->permission('edit_service_provider');
        abort_403(!in_array($this->editPermission, ['all', 'added']));

        $this->serviceProvider = ServiceProvider::findOrFail($id);
        $this->view = 'travel.service-providers.ajax.edit';

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return view('travel.service-providers.create', $this->data);
    }

    public function update(Request $request, $id)
    {
        $this->editPermission = user()->permission('edit_service_provider');
        abort_403(!in_array($this->editPermission, ['all', 'added']));

        $request->validate([
            'name' => 'required|max:255',
            'type' => 'required|in:hotel,transporter,airline,visa,iata,other',
            'email' => 'nullable|email|max:255',
        ]);

        $serviceProvider = ServiceProvider::findOrFail($id);
        $serviceProvider->name = $request->name;
        $serviceProvider->type = $request->type;
        $serviceProvider->contact_person = $request->contact_person;
        $serviceProvider->email = $request->email;
        $serviceProvider->phone = $request->phone;
        $serviceProvider->address = $request->address;
        $serviceProvider->status = $request->status ?? 'active';
        $serviceProvider->save();

        return Reply::successWithData(__('messages.updateSuccess'), ['redirectUrl' => route('service-providers.index')]);
    }

    public function destroy($id)
    {
        $this->deletePermission = user()->permission('delete_service_provider');
        abort_403(!in_array($this->deletePermission, ['all', 'added']));

        ServiceProvider::destroy($id);

        return Reply::successWithData(__('messages.deleteSuccess'), ['redirectUrl' => route('service-providers.index')]);
    }

}
