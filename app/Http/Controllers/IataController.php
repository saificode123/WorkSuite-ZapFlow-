<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use Illuminate\Http\Request;
use App\Models\IataRecord;
use App\DataTables\Travel\IataDataTable;

class IataController extends AccountBaseController
{

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.iata';

        $this->middleware(function ($request, $next) {
            abort_403(!in_array('iata', $this->user->modules));
            return $next($request);
        });
    }

    public function index(IataDataTable $dataTable)
    {
        $viewPermission = user()->permission('view_iata');
        abort_403(!in_array($viewPermission, ['all', 'added', 'owned', 'both']));

        return $dataTable->render('travel.iata.index', $this->data);
    }

    public function create()
    {
        $this->addPermission = user()->permission('add_iata');
        abort_403(!in_array($this->addPermission, ['all', 'added']));

        $this->view = 'travel.iata.ajax.create';

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return view('travel.iata.create', $this->data);
    }

    public function store(Request $request)
    {
        $this->addPermission = user()->permission('add_iata');
        abort_403(!in_array($this->addPermission, ['all', 'added']));

        $request->validate([
            'iata_code' => 'required|max:50',
            'name' => 'required|max:255',
        ]);

        $iata = new IataRecord();
        $iata->iata_code = $request->iata_code;
        $iata->name = $request->name;
        $iata->status = $request->status ?? 'active';
        $iata->save();

        $redirectUrl = urldecode($request->redirect_url);

        if ($redirectUrl == '') {
            $redirectUrl = route('iata.index');
        }

        return Reply::successWithData(__('messages.recordSaved'), ['redirectUrl' => $redirectUrl]);
    }

    public function edit($id)
    {
        $this->editPermission = user()->permission('edit_iata');
        abort_403(!in_array($this->editPermission, ['all', 'added']));

        $this->iata = IataRecord::with('accounts')->findOrFail($id);
        $this->view = 'travel.iata.ajax.edit';

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return view('travel.iata.create', $this->data);
    }

    public function update(Request $request, $id)
    {
        $this->editPermission = user()->permission('edit_iata');
        abort_403(!in_array($this->editPermission, ['all', 'added']));

        $request->validate([
            'iata_code' => 'required|max:50',
            'name' => 'required|max:255',
        ]);

        $iata = IataRecord::findOrFail($id);
        $iata->iata_code = $request->iata_code;
        $iata->name = $request->name;
        $iata->status = $request->status ?? 'active';
        $iata->save();

        return Reply::successWithData(__('messages.updateSuccess'), ['redirectUrl' => route('iata.index')]);
    }

    public function destroy($id)
    {
        $this->deletePermission = user()->permission('delete_iata');
        abort_403(!in_array($this->deletePermission, ['all', 'added']));

        IataRecord::destroy($id);

        return Reply::successWithData(__('messages.deleteSuccess'), ['redirectUrl' => route('iata.index')]);
    }

}
