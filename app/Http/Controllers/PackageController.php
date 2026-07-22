<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use App\Models\Package;
use App\Models\Hotel;
use App\DataTables\Travel\PackageDataTable;
use App\Services\PackageCalculationService;
use Illuminate\Http\Request;

class PackageController extends AccountBaseController
{

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.packages';

        $this->middleware(function ($request, $next) {
            abort_403(!in_array('package', $this->user->modules));
            return $next($request);
        });
    }

    public function index(PackageDataTable $dataTable)
    {
        $viewPermission = user()->permission('view_package');
        abort_403(!in_array($viewPermission, ['all', 'added', 'owned', 'both']));

        return $dataTable->render('travel.packages.index', $this->data);
    }

    public function create()
    {
        $this->addPermission = user()->permission('add_package');
        abort_403(!in_array($this->addPermission, ['all', 'added']));

        $this->hotels = Hotel::all();
        $this->view = 'travel.packages.ajax.create';

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return view('travel.packages.create', $this->data);
    }

    public function store(Request $request)
    {
        $this->addPermission = user()->permission('add_package');
        abort_403(!in_array($this->addPermission, ['all', 'added']));

        $request->validate([
            'name' => 'required|max:255',
            'type' => 'nullable|max:100',
            'duration_days' => 'nullable|integer',
            'price' => 'nullable|numeric',
        ]);

        $package = new Package();
        $package->name = $request->name;
        $package->type = $request->type;
        $package->duration_days = $request->duration_days;
        $package->price = $request->price;
        $package->description = $request->description;
        $package->save();

        if ($request->has('hotel_ids')) {
            $package->hotels()->sync($request->hotel_ids);
        }

        if ($request->boolean('auto_calculate') && $request->has('hotel_ids')) {
            app(PackageCalculationService::class)->recalculateAndSave($package);
        }

        $redirectUrl = urldecode($request->redirect_url);

        if ($redirectUrl == '') {
            $redirectUrl = route('packages.index');
        }

        return Reply::successWithData(__('messages.recordSaved'), ['redirectUrl' => $redirectUrl]);
    }

    public function edit($id)
    {
        $this->editPermission = user()->permission('edit_package');
        abort_403(!in_array($this->editPermission, ['all', 'added']));

        $this->package = Package::with('hotels')->findOrFail($id);
        $this->hotels = Hotel::all();
        $this->view = 'travel.packages.ajax.edit';

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return view('travel.packages.create', $this->data);
    }

    public function update(Request $request, $id)
    {
        $this->editPermission = user()->permission('edit_package');
        abort_403(!in_array($this->editPermission, ['all', 'added']));

        $request->validate([
            'name' => 'required|max:255',
            'type' => 'nullable|max:100',
            'duration_days' => 'nullable|integer',
            'price' => 'nullable|numeric',
        ]);

        $package = Package::findOrFail($id);
        $package->name = $request->name;
        $package->type = $request->type;
        $package->duration_days = $request->duration_days;
        $package->price = $request->price;
        $package->description = $request->description;
        $package->save();

        if ($request->has('hotel_ids')) {
            $package->hotels()->sync($request->hotel_ids);
        }

        return Reply::successWithData(__('messages.updateSuccess'), ['redirectUrl' => route('packages.index')]);
    }

    public function destroy($id)
    {
        $this->deletePermission = user()->permission('delete_package');
        abort_403(!in_array($this->deletePermission, ['all', 'added']));

        Package::destroy($id);

        return Reply::successWithData(__('messages.deleteSuccess'), ['redirectUrl' => route('packages.index')]);
    }

}
