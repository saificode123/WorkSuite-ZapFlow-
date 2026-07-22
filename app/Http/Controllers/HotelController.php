<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use Illuminate\Http\Request;
use App\Models\Hotel;
use App\DataTables\Travel\HotelDataTable;

class HotelController extends AccountBaseController
{

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.hotels';

        $this->middleware(function ($request, $next) {
            abort_403(!in_array('hotel', $this->user->modules));
            return $next($request);
        });
    }

    public function index(HotelDataTable $dataTable)
    {
        $viewPermission = user()->permission('view_hotel');
        abort_403(!in_array($viewPermission, ['all', 'added', 'owned', 'both']));

        return $dataTable->render('travel.hotels.index', $this->data);
    }

    public function create()
    {
        $this->addPermission = user()->permission('add_hotel');
        abort_403(!in_array($this->addPermission, ['all', 'added']));

        $this->view = 'travel.hotels.ajax.create';

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return view('travel.hotels.create', $this->data);
    }

    public function store(Request $request)
    {
        $this->addPermission = user()->permission('add_hotel');
        abort_403(!in_array($this->addPermission, ['all', 'added']));

        $request->validate([
            'name' => 'required|max:255',
            'star_rating' => 'nullable|integer|min:1|max:5',
            'contact_email' => 'nullable|email|max:255',
        ]);

        $hotel = new Hotel();
        $hotel->name = $request->name;
        $hotel->star_rating = $request->star_rating;
        $hotel->city = $request->city;
        $hotel->country = $request->country;
        $hotel->contact_email = $request->contact_email;
        $hotel->contact_phone = $request->contact_phone;
        $hotel->save();

        $redirectUrl = urldecode($request->redirect_url);

        if ($redirectUrl == '') {
            $redirectUrl = route('hotels.index');
        }

        return Reply::successWithData(__('messages.recordSaved'), ['redirectUrl' => $redirectUrl]);
    }

    public function edit($id)
    {
        $this->editPermission = user()->permission('edit_hotel');
        abort_403(!in_array($this->editPermission, ['all', 'added']));

        $this->hotel = Hotel::findOrFail($id);
        $this->view = 'travel.hotels.ajax.edit';

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return view('travel.hotels.create', $this->data);
    }

    public function update(Request $request, $id)
    {
        $this->editPermission = user()->permission('edit_hotel');
        abort_403(!in_array($this->editPermission, ['all', 'added']));

        $request->validate([
            'name' => 'required|max:255',
            'star_rating' => 'nullable|integer|min:1|max:5',
            'contact_email' => 'nullable|email|max:255',
        ]);

        $hotel = Hotel::findOrFail($id);
        $hotel->name = $request->name;
        $hotel->star_rating = $request->star_rating;
        $hotel->city = $request->city;
        $hotel->country = $request->country;
        $hotel->contact_email = $request->contact_email;
        $hotel->contact_phone = $request->contact_phone;
        $hotel->save();

        return Reply::successWithData(__('messages.updateSuccess'), ['redirectUrl' => route('hotels.index')]);
    }

    public function destroy($id)
    {
        $this->deletePermission = user()->permission('delete_hotel');
        abort_403(!in_array($this->deletePermission, ['all', 'added']));

        Hotel::destroy($id);

        return Reply::successWithData(__('messages.deleteSuccess'), ['redirectUrl' => route('hotels.index')]);
    }

}
