<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use Illuminate\Http\Request;
use App\Models\Airline;
use App\DataTables\Travel\AirlineDataTable;

class AirlineController extends AccountBaseController
{

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.airlines';

        $this->middleware(function ($request, $next) {
            abort_403(!in_array('airline', $this->user->modules));
            return $next($request);
        });
    }

    public function index(AirlineDataTable $dataTable)
    {
        $viewPermission = user()->permission('view_airline');
        abort_403(!in_array($viewPermission, ['all', 'added', 'owned', 'both']));

        return $dataTable->render('travel.airlines.index', $this->data);
    }

    public function create()
    {
        $this->addPermission = user()->permission('add_airline');
        abort_403(!in_array($this->addPermission, ['all', 'added']));

        $this->view = 'travel.airlines.ajax.create';

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return view('travel.airlines.create', $this->data);
    }

    public function store(Request $request)
    {
        $this->addPermission = user()->permission('add_airline');
        abort_403(!in_array($this->addPermission, ['all', 'added']));

        $request->validate([
            'name' => 'required|max:255',
            'iata_code' => 'nullable|max:10',
            'contact_email' => 'nullable|email|max:255',
            'website' => 'nullable|url|max:255',
        ]);

        $airline = new Airline();
        $airline->name = $request->name;
        $airline->iata_code = $request->iata_code;
        $airline->logo = $request->logo;
        $airline->contact_phone = $request->contact_phone;
        $airline->contact_email = $request->contact_email;
        $airline->website = $request->website;
        $airline->save();

        $redirectUrl = urldecode($request->redirect_url);

        if ($redirectUrl == '') {
            $redirectUrl = route('airlines.index');
        }

        return Reply::successWithData(__('messages.recordSaved'), ['redirectUrl' => $redirectUrl]);
    }

    public function edit($id)
    {
        $this->editPermission = user()->permission('edit_airline');
        abort_403(!in_array($this->editPermission, ['all', 'added']));

        $this->airline = Airline::findOrFail($id);
        $this->view = 'travel.airlines.ajax.edit';

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return view('travel.airlines.create', $this->data);
    }

    public function update(Request $request, $id)
    {
        $this->editPermission = user()->permission('edit_airline');
        abort_403(!in_array($this->editPermission, ['all', 'added']));

        $request->validate([
            'name' => 'required|max:255',
            'iata_code' => 'nullable|max:10',
            'contact_email' => 'nullable|email|max:255',
            'website' => 'nullable|url|max:255',
        ]);

        $airline = Airline::findOrFail($id);
        $airline->name = $request->name;
        $airline->iata_code = $request->iata_code;
        $airline->logo = $request->logo;
        $airline->contact_phone = $request->contact_phone;
        $airline->contact_email = $request->contact_email;
        $airline->website = $request->website;
        $airline->save();

        return Reply::successWithData(__('messages.updateSuccess'), ['redirectUrl' => route('airlines.index')]);
    }

    public function destroy($id)
    {
        $this->deletePermission = user()->permission('delete_airline');
        abort_403(!in_array($this->deletePermission, ['all', 'added']));

        Airline::destroy($id);

        return Reply::successWithData(__('messages.deleteSuccess'), ['redirectUrl' => route('airlines.index')]);
    }

}
