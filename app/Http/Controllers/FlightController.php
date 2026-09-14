<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use Illuminate\Http\Request;
use App\Models\Flight;
use App\Models\Airline;
use App\DataTables\Travel\FlightDataTable;

class FlightController extends AccountBaseController
{

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.flights';

        $this->middleware(function ($request, $next) {
            abort_403(!in_array('ticketing', $this->user->modules));
            return $next($request);
        });
    }

    public function index(FlightDataTable $dataTable)
    {
        $viewPermission = user()->permission('view_flight');
        abort_403(!in_array($viewPermission, ['all', 'added', 'owned', 'both']));

        return $dataTable->render('travel.flights.index', $this->data);
    }

    public function create()
    {
        $this->addPermission = user()->permission('add_flight');
        abort_403(!in_array($this->addPermission, ['all', 'added']));

        $this->airlines = Airline::all();
        $this->view = 'travel.flights.ajax.create';

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return view('travel.flights.create', $this->data);
    }

    public function store(Request $request)
    {
        $this->addPermission = user()->permission('add_flight');
        abort_403(!in_array($this->addPermission, ['all', 'added']));

        $request->validate([
            'flight_number' => 'required|max:50',
            'airline_id' => 'nullable|exists:airlines,id',
            'origin' => 'required|max:255',
            'destination' => 'required|max:255',
        ]);

        $flight = new Flight();
        $flight->flight_number = $request->flight_number;
        $flight->airline_id = $request->airline_id;
        $flight->origin = $request->origin;
        $flight->destination = $request->destination;
        $flight->departure_time = $request->departure_time;
        $flight->arrival_time = $request->arrival_time;
        $flight->save();

        $redirectUrl = urldecode($request->redirect_url);

        if ($redirectUrl == '') {
            $redirectUrl = route('flights.index');
        }

        return Reply::successWithData(__('messages.recordSaved'), ['redirectUrl' => $redirectUrl]);
    }

    public function edit($id)
    {
        $this->editPermission = user()->permission('edit_flight');
        abort_403(!in_array($this->editPermission, ['all', 'added']));

        $this->flight = Flight::findOrFail($id);
        $this->airlines = Airline::all();
        $this->view = 'travel.flights.ajax.edit';

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return view('travel.flights.create', $this->data);
    }

    public function update(Request $request, $id)
    {
        $this->editPermission = user()->permission('edit_flight');
        abort_403(!in_array($this->editPermission, ['all', 'added']));

        $request->validate([
            'flight_number' => 'required|max:50',
            'airline_id' => 'nullable|exists:airlines,id',
            'origin' => 'required|max:255',
            'destination' => 'required|max:255',
        ]);

        $flight = Flight::findOrFail($id);
        $flight->flight_number = $request->flight_number;
        $flight->airline_id = $request->airline_id;
        $flight->origin = $request->origin;
        $flight->destination = $request->destination;
        $flight->departure_time = $request->departure_time;
        $flight->arrival_time = $request->arrival_time;
        $flight->save();

        return Reply::successWithData(__('messages.updateSuccess'), ['redirectUrl' => route('flights.index')]);
    }

    public function destroy($id)
    {
        $this->deletePermission = user()->permission('delete_flight');
        abort_403(!in_array($this->deletePermission, ['all', 'added']));

        Flight::destroy($id);

        return Reply::successWithData(__('messages.deleteSuccess'), ['redirectUrl' => route('flights.index')]);
    }

}
