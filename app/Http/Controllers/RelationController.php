<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use Illuminate\Http\Request;
use App\Models\Relation;
use App\DataTables\Travel\RelationDataTable;

class RelationController extends AccountBaseController
{

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.relations';

        $this->middleware(function ($request, $next) {
            abort_403(!in_array('umrah_setup', $this->user->modules));
            return $next($request);
        });
    }

    public function index(RelationDataTable $dataTable)
    {
        $viewPermission = user()->permission('view_relation');
        abort_403(!in_array($viewPermission, ['all', 'added', 'owned', 'both']));

        return $dataTable->render('travel.relations.index', $this->data);
    }

    public function create()
    {
        $this->addPermission = user()->permission('add_relation');
        abort_403(!in_array($this->addPermission, ['all', 'added']));

        $this->view = 'travel.relations.ajax.create';

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return view('travel.relations.create', $this->data);
    }

    public function store(Request $request)
    {
        $this->addPermission = user()->permission('add_relation');
        abort_403(!in_array($this->addPermission, ['all', 'added']));

        $request->validate([
            'name' => 'required|max:255',
        ]);

        $relation = new Relation();
        $relation->name = $request->name;
        $relation->save();

        $redirectUrl = urldecode($request->redirect_url);

        if ($redirectUrl == '') {
            $redirectUrl = route('relations.index');
        }

        return Reply::successWithData(__('messages.recordSaved'), ['redirectUrl' => $redirectUrl]);
    }

    public function edit($id)
    {
        $this->editPermission = user()->permission('edit_relation');
        abort_403(!in_array($this->editPermission, ['all', 'added']));

        $this->relation = Relation::findOrFail($id);
        $this->view = 'travel.relations.ajax.edit';

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return view('travel.relations.create', $this->data);
    }

    public function update(Request $request, $id)
    {
        $this->editPermission = user()->permission('edit_relation');
        abort_403(!in_array($this->editPermission, ['all', 'added']));

        $request->validate([
            'name' => 'required|max:255',
        ]);

        $relation = Relation::findOrFail($id);
        $relation->name = $request->name;
        $relation->save();

        return Reply::successWithData(__('messages.updateSuccess'), ['redirectUrl' => route('relations.index')]);
    }

    public function destroy($id)
    {
        $this->deletePermission = user()->permission('delete_relation');
        abort_403(!in_array($this->deletePermission, ['all', 'added']));

        Relation::destroy($id);

        return Reply::successWithData(__('messages.deleteSuccess'), ['redirectUrl' => route('relations.index')]);
    }

}
