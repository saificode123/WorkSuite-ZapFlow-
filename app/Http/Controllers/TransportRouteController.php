<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use Illuminate\Http\Request;
use App\Models\TransportRoute;
use App\DataTables\Travel\TransportRouteDataTable;

class TransportRouteController extends AccountBaseController
{

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.transportRoutes';

        $this->middleware(function ($request, $next) {
            abort_403(!in_array('transport_route', $this->user->modules));
            return $next($request);
        });
    }

    public function index(TransportRouteDataTable $dataTable)
    {
        $viewPermission = user()->permission('view_transport_route');
        abort_403(!in_array($viewPermission, ['all', 'added', 'owned', 'both']));

        return $dataTable->render('travel.transport-routes.index', $this->data);
    }

    public function create()
    {
        $this->addPermission = user()->permission('add_transport_route');
        abort_403(!in_array($this->addPermission, ['all', 'added']));

        $this->view = 'travel.transport-routes.ajax.create';

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return view('travel.transport-routes.create', $this->data);
    }

    public function store(Request $request)
    {
        $this->addPermission = user()->permission('add_transport_route');
        abort_403(!in_array($this->addPermission, ['all', 'added']));

        $request->validate([
            'from_city' => 'required|max:255',
            'to_city' => 'required|max:255',
            'distance_km' => 'nullable|numeric|min:0',
        ]);

        $route = new TransportRoute();
        $route->from_city = $request->from_city;
        $route->to_city = $request->to_city;
        $route->distance_km = $request->distance_km;
        $route->save();

        $redirectUrl = urldecode($request->redirect_url);

        if ($redirectUrl == '') {
            $redirectUrl = route('transport-routes.index');
        }

        return Reply::successWithData(__('messages.recordSaved'), ['redirectUrl' => $redirectUrl]);
    }

    public function edit($id)
    {
        $this->editPermission = user()->permission('edit_transport_route');
        abort_403(!in_array($this->editPermission, ['all', 'added']));

        $this->transportRoute = TransportRoute::findOrFail($id);
        $this->view = 'travel.transport-routes.ajax.edit';

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return view('travel.transport-routes.create', $this->data);
    }

    public function update(Request $request, $id)
    {
        $this->editPermission = user()->permission('edit_transport_route');
        abort_403(!in_array($this->editPermission, ['all', 'added']));

        $request->validate([
            'from_city' => 'required|max:255',
            'to_city' => 'required|max:255',
            'distance_km' => 'nullable|numeric|min:0',
        ]);

        $route = TransportRoute::findOrFail($id);
        $route->from_city = $request->from_city;
        $route->to_city = $request->to_city;
        $route->distance_km = $request->distance_km;
        $route->save();

        return Reply::successWithData(__('messages.updateSuccess'), ['redirectUrl' => route('transport-routes.index')]);
    }

    public function destroy($id)
    {
        $this->deletePermission = user()->permission('delete_transport_route');
        abort_403(!in_array($this->deletePermission, ['all', 'added']));

        TransportRoute::destroy($id);

        return Reply::successWithData(__('messages.deleteSuccess'), ['redirectUrl' => route('transport-routes.index')]);
    }

}
