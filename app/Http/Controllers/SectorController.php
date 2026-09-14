<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use Illuminate\Http\Request;
use App\Models\Sector;
use App\DataTables\Travel\SectorDataTable;

class SectorController extends AccountBaseController
{

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.sectors';

        $this->middleware(function ($request, $next) {
            abort_403(!in_array('umrah_setup', $this->user->modules));
            return $next($request);
        });
    }

    public function index(SectorDataTable $dataTable)
    {
        $viewPermission = user()->permission('view_sector');
        abort_403(!in_array($viewPermission, ['all', 'added', 'owned', 'both']));

        return $dataTable->render('travel.sectors.index', $this->data);
    }

    public function create()
    {
        $this->addPermission = user()->permission('add_sector');
        abort_403(!in_array($this->addPermission, ['all', 'added']));

        $this->view = 'travel.sectors.ajax.create';

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return view('travel.sectors.create', $this->data);
    }

    public function store(Request $request)
    {
        $this->addPermission = user()->permission('add_sector');
        abort_403(!in_array($this->addPermission, ['all', 'added']));

        $request->validate([
            'name' => 'required|max:255',
            'code' => 'nullable|max:20',
        ]);

        $sector = new Sector();
        $sector->name = $request->name;
        $sector->code = $request->code;
        $sector->save();

        $redirectUrl = urldecode($request->redirect_url);

        if ($redirectUrl == '') {
            $redirectUrl = route('sectors.index');
        }

        return Reply::successWithData(__('messages.recordSaved'), ['redirectUrl' => $redirectUrl]);
    }

    public function edit($id)
    {
        $this->editPermission = user()->permission('edit_sector');
        abort_403(!in_array($this->editPermission, ['all', 'added']));

        $this->sector = Sector::findOrFail($id);
        $this->view = 'travel.sectors.ajax.edit';

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return view('travel.sectors.create', $this->data);
    }

    public function update(Request $request, $id)
    {
        $this->editPermission = user()->permission('edit_sector');
        abort_403(!in_array($this->editPermission, ['all', 'added']));

        $request->validate([
            'name' => 'required|max:255',
            'code' => 'nullable|max:20',
        ]);

        $sector = Sector::findOrFail($id);
        $sector->name = $request->name;
        $sector->code = $request->code;
        $sector->save();

        return Reply::successWithData(__('messages.updateSuccess'), ['redirectUrl' => route('sectors.index')]);
    }

    public function destroy($id)
    {
        $this->deletePermission = user()->permission('delete_sector');
        abort_403(!in_array($this->deletePermission, ['all', 'added']));

        Sector::destroy($id);

        return Reply::successWithData(__('messages.deleteSuccess'), ['redirectUrl' => route('sectors.index')]);
    }

}
