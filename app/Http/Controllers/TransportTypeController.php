<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use Illuminate\Http\Request;
use App\Models\TransportType;
use App\DataTables\Travel\TransportTypeDataTable;

class TransportTypeController extends AccountBaseController
{

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.transportTypes';

        $this->middleware(function ($request, $next) {
            abort_403(!in_array('umrah_setup', $this->user->modules));
            return $next($request);
        });
    }

    public function index(TransportTypeDataTable $dataTable)
    {
        $viewPermission = user()->permission('view_transport_type');
        abort_403(!in_array($viewPermission, ['all', 'added', 'owned', 'both']));

        return $dataTable->render('travel.transport-types.index', $this->data);
    }

    public function create()
    {
        $this->addPermission = user()->permission('add_transport_type');
        abort_403(!in_array($this->addPermission, ['all', 'added']));

        $this->view = 'travel.transport-types.ajax.create';

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return view('travel.transport-types.create', $this->data);
    }

    public function store(Request $request)
    {
        $this->addPermission = user()->permission('add_transport_type');
        abort_403(!in_array($this->addPermission, ['all', 'added']));

        $request->validate([
            'name' => 'required|max:255',
            'capacity' => 'nullable|integer|min:1',
        ]);

        $transportType = new TransportType();
        $transportType->name = $request->name;
        $transportType->capacity = $request->capacity;
        $transportType->save();

        $redirectUrl = urldecode($request->redirect_url);

        if ($redirectUrl == '') {
            $redirectUrl = route('transport-types.index');
        }

        return Reply::successWithData(__('messages.recordSaved'), ['redirectUrl' => $redirectUrl]);
    }

    public function edit($id)
    {
        $this->editPermission = user()->permission('edit_transport_type');
        abort_403(!in_array($this->editPermission, ['all', 'added']));

        $this->transportType = TransportType::findOrFail($id);
        $this->view = 'travel.transport-types.ajax.edit';

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return view('travel.transport-types.create', $this->data);
    }

    public function update(Request $request, $id)
    {
        $this->editPermission = user()->permission('edit_transport_type');
        abort_403(!in_array($this->editPermission, ['all', 'added']));

        $request->validate([
            'name' => 'required|max:255',
            'capacity' => 'nullable|integer|min:1',
        ]);

        $transportType = TransportType::findOrFail($id);
        $transportType->name = $request->name;
        $transportType->capacity = $request->capacity;
        $transportType->save();

        return Reply::successWithData(__('messages.updateSuccess'), ['redirectUrl' => route('transport-types.index')]);
    }

    public function destroy($id)
    {
        $this->deletePermission = user()->permission('delete_transport_type');
        abort_403(!in_array($this->deletePermission, ['all', 'added']));

        TransportType::destroy($id);

        return Reply::successWithData(__('messages.deleteSuccess'), ['redirectUrl' => route('transport-types.index')]);
    }

}
