<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use Illuminate\Http\Request;
use App\Models\Discount;
use App\DataTables\Travel\DiscountDataTable;

class DiscountController extends AccountBaseController
{

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.discounts';

        $this->middleware(function ($request, $next) {
            abort_403(!in_array('umrah_setup', $this->user->modules));
            return $next($request);
        });
    }

    public function index(DiscountDataTable $dataTable)
    {
        $viewPermission = user()->permission('view_discount');
        abort_403(!in_array($viewPermission, ['all', 'added', 'owned', 'both']));

        return $dataTable->render('travel.discounts.index', $this->data);
    }

    public function create()
    {
        $this->addPermission = user()->permission('add_discount');
        abort_403(!in_array($this->addPermission, ['all', 'added']));

        $this->view = 'travel.discounts.ajax.create';

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return view('travel.discounts.create', $this->data);
    }

    public function store(Request $request)
    {
        $this->addPermission = user()->permission('add_discount');
        abort_403(!in_array($this->addPermission, ['all', 'added']));

        $request->validate([
            'name' => 'required|max:255',
            'discount_type' => 'required|in:percentage,fixed',
            'value' => 'required|numeric|min:0',
        ]);

        $discount = new Discount();
        $discount->name = $request->name;
        $discount->discount_type = $request->discount_type;
        $discount->value = $request->value;
        $discount->is_active = $request->is_active ?? true;
        $discount->save();

        $redirectUrl = urldecode($request->redirect_url);

        if ($redirectUrl == '') {
            $redirectUrl = route('discounts.index');
        }

        return Reply::successWithData(__('messages.recordSaved'), ['redirectUrl' => $redirectUrl]);
    }

    public function edit($id)
    {
        $this->editPermission = user()->permission('edit_discount');
        abort_403(!in_array($this->editPermission, ['all', 'added']));

        $this->discount = Discount::findOrFail($id);
        $this->view = 'travel.discounts.ajax.edit';

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return view('travel.discounts.create', $this->data);
    }

    public function update(Request $request, $id)
    {
        $this->editPermission = user()->permission('edit_discount');
        abort_403(!in_array($this->editPermission, ['all', 'added']));

        $request->validate([
            'name' => 'required|max:255',
            'discount_type' => 'required|in:percentage,fixed',
            'value' => 'required|numeric|min:0',
        ]);

        $discount = Discount::findOrFail($id);
        $discount->name = $request->name;
        $discount->discount_type = $request->discount_type;
        $discount->value = $request->value;
        $discount->is_active = $request->is_active ?? true;
        $discount->save();

        return Reply::successWithData(__('messages.updateSuccess'), ['redirectUrl' => route('discounts.index')]);
    }

    public function destroy($id)
    {
        $this->deletePermission = user()->permission('delete_discount');
        abort_403(!in_array($this->deletePermission, ['all', 'added']));

        Discount::destroy($id);

        return Reply::successWithData(__('messages.deleteSuccess'), ['redirectUrl' => route('discounts.index')]);
    }

}
