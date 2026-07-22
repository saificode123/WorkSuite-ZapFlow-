<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use Illuminate\Http\Request;
use App\Models\TicketInvoice;
use App\DataTables\Travel\TicketInvoiceDataTable;

class TicketInvoiceController extends AccountBaseController
{

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.ticketing';

        $this->middleware(function ($request, $next) {
            abort_403(!in_array('ticket_invoice', $this->user->modules));
            return $next($request);
        });
    }

    public function index(TicketInvoiceDataTable $dataTable)
    {
        $viewPermission = user()->permission('view_ticket_invoice');
        abort_403(!in_array($viewPermission, ['all', 'added', 'owned', 'both']));

        return $dataTable->render('travel.ticketing.index', $this->data);
    }

    public function create()
    {
        $this->addPermission = user()->permission('add_ticket_invoice');
        abort_403(!in_array($this->addPermission, ['all', 'added']));

        $this->view = 'travel.ticketing.ajax.create';

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return view('travel.ticketing.create', $this->data);
    }

    public function store(Request $request)
    {
        $this->addPermission = user()->permission('add_ticket_invoice');
        abort_403(!in_array($this->addPermission, ['all', 'added']));

        $request->validate([
            'invoice_number' => 'required|max:255|unique:ticket_invoices,invoice_number',
            'date' => 'nullable|date',
            'total_amount' => 'nullable|numeric',
            'ticket_count' => 'nullable|integer|min:1',
        ]);

        $ticketInvoice = new TicketInvoice();
        $ticketInvoice->invoice_number = $request->invoice_number;
        $ticketInvoice->date = $request->date;
        $ticketInvoice->booking_group_id = $request->booking_group_id;
        $ticketInvoice->customer_id = $request->customer_id;
        $ticketInvoice->airline_id = $request->airline_id;
        $ticketInvoice->passenger_id = $request->passenger_id;
        $ticketInvoice->sector_id = $request->sector_id;
        $ticketInvoice->total_amount = $request->total_amount;
        $ticketInvoice->status = $request->status ?? 'pending';
        $ticketInvoice->ticket_count = $request->ticket_count ?? 1;
        $ticketInvoice->save();

        $redirectUrl = urldecode($request->redirect_url);

        if ($redirectUrl == '') {
            $redirectUrl = route('ticketing.index');
        }

        return Reply::successWithData(__('messages.recordSaved'), ['redirectUrl' => $redirectUrl]);
    }

    public function edit($id)
    {
        $this->editPermission = user()->permission('edit_ticket_invoice');
        abort_403(!in_array($this->editPermission, ['all', 'added']));

        $this->ticketInvoice = TicketInvoice::findOrFail($id);
        $this->view = 'travel.ticketing.ajax.edit';

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return view('travel.ticketing.create', $this->data);
    }

    public function update(Request $request, $id)
    {
        $this->editPermission = user()->permission('edit_ticket_invoice');
        abort_403(!in_array($this->editPermission, ['all', 'added']));

        $request->validate([
            'invoice_number' => 'required|max:255|unique:ticket_invoices,invoice_number,' . $id,
            'date' => 'nullable|date',
            'total_amount' => 'nullable|numeric',
            'ticket_count' => 'nullable|integer|min:1',
        ]);

        $ticketInvoice = TicketInvoice::findOrFail($id);
        $ticketInvoice->invoice_number = $request->invoice_number;
        $ticketInvoice->date = $request->date;
        $ticketInvoice->booking_group_id = $request->booking_group_id;
        $ticketInvoice->customer_id = $request->customer_id;
        $ticketInvoice->airline_id = $request->airline_id;
        $ticketInvoice->passenger_id = $request->passenger_id;
        $ticketInvoice->sector_id = $request->sector_id;
        $ticketInvoice->total_amount = $request->total_amount;
        $ticketInvoice->status = $request->status ?? 'pending';
        $ticketInvoice->ticket_count = $request->ticket_count ?? 1;
        $ticketInvoice->save();

        return Reply::successWithData(__('messages.updateSuccess'), ['redirectUrl' => route('ticketing.index')]);
    }

    public function destroy($id)
    {
        $this->deletePermission = user()->permission('delete_ticket_invoice');
        abort_403(!in_array($this->deletePermission, ['all', 'added']));

        TicketInvoice::destroy($id);

        return Reply::successWithData(__('messages.deleteSuccess'), ['redirectUrl' => route('ticketing.index')]);
    }

}
