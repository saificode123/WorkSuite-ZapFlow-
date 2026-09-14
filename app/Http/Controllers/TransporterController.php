<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use Illuminate\Http\Request;
use App\Models\Transporter;
use App\DataTables\Travel\TransporterDataTable;

class TransporterController extends AccountBaseController
{

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.transporters';

        $this->middleware(function ($request, $next) {
            abort_403(!in_array('umrah_setup', $this->user->modules));
            return $next($request);
        });
    }

    public function index(TransporterDataTable $dataTable)
    {
        $viewPermission = user()->permission('view_transporter');
        abort_403(!in_array($viewPermission, ['all', 'added', 'owned', 'both']));

        return $dataTable->render('travel.transporters.index', $this->data);
    }

    public function create()
    {
        $this->addPermission = user()->permission('add_transporter');
        abort_403(!in_array($this->addPermission, ['all', 'added']));

        $this->view = 'travel.transporters.ajax.create';

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return view('travel.transporters.create', $this->data);
    }

    public function store(Request $request)
    {
        $this->addPermission = user()->permission('add_transporter');
        abort_403(!in_array($this->addPermission, ['all', 'added']));

        $request->validate([
            'name' => 'required|max:255',
            'email' => 'nullable|email|max:255',
        ]);

        $transporter = new Transporter();
        $transporter->name = $request->name;
        $transporter->contact_person = $request->contact_person;
        $transporter->phone = $request->phone;
        $transporter->email = $request->email;
        $transporter->vehicle_types = $request->vehicle_types;
        $transporter->save();

        $redirectUrl = urldecode($request->redirect_url);

        if ($redirectUrl == '') {
            $redirectUrl = route('transporters.index');
        }

        return Reply::successWithData(__('messages.recordSaved'), ['redirectUrl' => $redirectUrl]);
    }

    public function edit($id)
    {
        $this->editPermission = user()->permission('edit_transporter');
        abort_403(!in_array($this->editPermission, ['all', 'added']));

        $this->transporter = Transporter::findOrFail($id);
        $this->view = 'travel.transporters.ajax.edit';

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return view('travel.transporters.create', $this->data);
    }

    public function update(Request $request, $id)
    {
        $this->editPermission = user()->permission('edit_transporter');
        abort_403(!in_array($this->editPermission, ['all', 'added']));

        $request->validate([
            'name' => 'required|max:255',
            'email' => 'nullable|email|max:255',
        ]);

        $transporter = Transporter::findOrFail($id);
        $transporter->name = $request->name;
        $transporter->contact_person = $request->contact_person;
        $transporter->phone = $request->phone;
        $transporter->email = $request->email;
        $transporter->vehicle_types = $request->vehicle_types;
        $transporter->save();

        return Reply::successWithData(__('messages.updateSuccess'), ['redirectUrl' => route('transporters.index')]);
    }

    public function destroy($id)
    {
        $this->deletePermission = user()->permission('delete_transporter');
        abort_403(!in_array($this->deletePermission, ['all', 'added']));

        Transporter::destroy($id);

        return Reply::successWithData(__('messages.deleteSuccess'), ['redirectUrl' => route('transporters.index')]);
    }

}
