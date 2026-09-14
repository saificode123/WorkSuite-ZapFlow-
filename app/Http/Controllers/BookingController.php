<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use Illuminate\Http\Request;
use App\Models\BookingGroup;
use App\Models\Package;
use App\Models\IataRecord;
use App\Models\Passenger;
use App\Models\Relation;
use App\DataTables\Travel\BookingDataTable;

class BookingController extends AccountBaseController
{

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.bookings';

        $this->middleware(function ($request, $next) {
            abort_403(!in_array('bookings', $this->user->modules));
            return $next($request);
        });
    }

    public function index(BookingDataTable $dataTable)
    {
        $viewPermission = user()->permission('view_booking');
        abort_403(!in_array($viewPermission, ['all', 'added', 'owned', 'both']));

        return $dataTable->render('travel.bookings.index', $this->data);
    }

    public function create()
    {
        $this->addPermission = user()->permission('add_booking');
        abort_403(!in_array($this->addPermission, ['all', 'added']));

        $this->packages = Package::all();
        $this->iataRecords = IataRecord::all();
        $this->view = 'travel.bookings.ajax.create';

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return view('travel.bookings.create', $this->data);
    }

    public function store(Request $request)
    {
        $this->addPermission = user()->permission('add_booking');
        abort_403(!in_array($this->addPermission, ['all', 'added']));

        $request->validate([
            'group_name' => 'required|max:255',
            'group_leader' => 'nullable|max:255',
            'departure_date' => 'nullable|date',
            'return_date' => 'nullable|date|after_or_equal:departure_date',
        ]);

        $booking = new BookingGroup();
        $booking->group_name = $request->group_name;
        $booking->group_leader = $request->group_leader;
        $booking->package_id = $request->package_id;
        $booking->iata_id = $request->iata_id;
        $booking->customer_id = $request->customer_id;
        $booking->total_pax = $request->total_pax ?? 0;
        $booking->departure_date = $request->departure_date;
        $booking->return_date = $request->return_date;
        $booking->status = $request->status ?? 'pending';
        $booking->save();

        $redirectUrl = urldecode($request->redirect_url);

        if ($redirectUrl == '') {
            $redirectUrl = route('bookings.index');
        }

        return Reply::successWithData(__('messages.recordSaved'), ['redirectUrl' => $redirectUrl]);
    }

    public function edit($id)
    {
        $this->editPermission = user()->permission('edit_booking');
        abort_403(!in_array($this->editPermission, ['all', 'added']));

        $this->booking = BookingGroup::with('passengers')->findOrFail($id);
        $this->packages = Package::all();
        $this->iataRecords = IataRecord::all();
        $this->view = 'travel.bookings.ajax.edit';

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return view('travel.bookings.create', $this->data);
    }

    public function update(Request $request, $id)
    {
        $this->editPermission = user()->permission('edit_booking');
        abort_403(!in_array($this->editPermission, ['all', 'added']));

        $request->validate([
            'group_name' => 'required|max:255',
            'group_leader' => 'nullable|max:255',
            'departure_date' => 'nullable|date',
            'return_date' => 'nullable|date|after_or_equal:departure_date',
        ]);

        $booking = BookingGroup::findOrFail($id);
        $booking->group_name = $request->group_name;
        $booking->group_leader = $request->group_leader;
        $booking->package_id = $request->package_id;
        $booking->iata_id = $request->iata_id;
        $booking->customer_id = $request->customer_id;
        $booking->total_pax = $request->total_pax ?? 0;
        $booking->departure_date = $request->departure_date;
        $booking->return_date = $request->return_date;
        $booking->status = $request->status ?? 'pending';
        $booking->save();

        return Reply::successWithData(__('messages.updateSuccess'), ['redirectUrl' => route('bookings.index')]);
    }

    public function destroy($id)
    {
        $this->deletePermission = user()->permission('delete_booking');
        abort_403(!in_array($this->deletePermission, ['all', 'added']));

        BookingGroup::destroy($id);

        return Reply::successWithData(__('messages.deleteSuccess'), ['redirectUrl' => route('bookings.index')]);
    }

    public function import(Request $request)
    {
        // redirect old route to new importPage
        return redirect()->route('bookings.import.page');
    }

    public function importPreview(Request $request)
    {
        return redirect()->route('bookings.import.page');
    }

    // ── Show / Detail ──────────────────────────────────────────────────────────

    public function show($id)
    {
        $viewPermission = user()->permission('view_booking');
        abort_403(!in_array($viewPermission, ['all', 'added', 'owned', 'both']));

        $this->booking = BookingGroup::with([
            'passengers.roomAllocation',
            'vouchers',
            'travelPayments',
            'package',
        ])->findOrFail($id);

        // Enforce 'owned' scope: user can only see bookings where they are the customer.
        if ($viewPermission === 'owned' && $this->booking->customer_id !== user()->id) {
            abort_403(true);
        }

        return view('travel.bookings.show', $this->data);
    }

    // ── Passenger Management ───────────────────────────────────────────────────

    public function addPassenger(Request $request, $bookingId)
    {
        $this->editPermission = user()->permission('edit_booking');
        abort_403(!in_array($this->editPermission, ['all', 'added']));

        $this->booking   = BookingGroup::findOrFail($bookingId);
        $this->relations = \App\Models\Relation::orderBy('name')->get();
        $this->view      = 'travel.bookings.ajax.add-passenger';

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }
        return view($this->view, $this->data);
    }

    public function storePassenger(Request $request, $bookingId)
    {
        $this->editPermission = user()->permission('edit_booking');
        abort_403(!in_array($this->editPermission, ['all', 'added']));

        $request->validate([
            'passport_no'  => 'required|string|max:50',
            'first_name'   => 'required|string|max:100',
            'family_name'  => 'required|string|max:100',
            'gender'       => 'required|in:Male,Female',
            'birth_date'   => 'nullable|date',
        ]);

        $booking = BookingGroup::findOrFail($bookingId);
        $booking->passengers()->create(array_merge($request->only([
            'passport_no', 'first_name', 'family_name', 'gender',
            'birth_date', 'relation_id', 'mofa_status',
        ]), [
            'visa_pipeline_status' => 'draft',
        ]));

        return Reply::successWithData(
            __('messages.recordSaved'),
            ['redirectUrl' => route('bookings.show', $bookingId)]
        );
    }

    public function removePassenger($bookingId, $passengerId)
    {
        $this->editPermission = user()->permission('edit_booking');
        abort_403(!in_array($this->editPermission, ['all', 'added']));

        Passenger::where('booking_group_id', $bookingId)
            ->where('id', $passengerId)
            ->delete();

        return Reply::success(__('messages.deleteSuccess'));
    }

    // ── Booking Import ─────────────────────────────────────────────────────────

    public function importPage(Request $request)
    {
        $this->addPermission = user()->permission('add_booking');
        abort_403(!in_array($this->addPermission, ['all', 'added']));

        $this->bookingGroups    = BookingGroup::orderBy('group_name')->get();
        $this->selectedBookingId = $request->booking_group_id;
        $this->preview          = null;

        return view('travel.bookings.import', $this->data);
    }

    public function importParse(Request $request)
    {
        $this->addPermission = user()->permission('add_booking');
        abort_403(!in_array($this->addPermission, ['all', 'added']));

        $request->validate([
            'file' => 'required_without:raw_content|file|mimes:csv,xlsx,xls|max:5120',
        ]);

        /** @var \App\Services\BookingImportService $importer */
        $importer = app(\App\Services\BookingImportService::class);

        if ($request->hasFile('file')) {
            $path   = $request->file('file')->store('temp-imports', 'local');
            $result = $importer->parseFile(storage_path('app/' . $path));
        } else {
            $result = $importer->parseContent($request->raw_content ?? '');
        }

        return response()->json([
            'status'  => empty($result['errors']) ? 'success' : 'partial',
            'data'    => $result,
        ]);
    }

    public function importCommit(Request $request)
    {
        $this->addPermission = user()->permission('add_booking');
        abort_403(!in_array($this->addPermission, ['all', 'added']));

        $request->validate([
            'booking_group_id' => 'required|exists:booking_groups,id',
            'passengers'       => 'required|array|min:1',
        ]);

        $booking    = BookingGroup::findOrFail($request->booking_group_id);
        $created    = 0;
        $skipped    = 0;

        foreach ($request->passengers as $paxData) {
            // Skip duplicates (same passport in same booking)
            $exists = $booking->passengers()
                ->where('passport_no', $paxData['passport_no'])
                ->exists();

            if ($exists) { $skipped++; continue; }

            $booking->passengers()->create([
                'passport_no'          => $paxData['passport_no'],
                'first_name'           => $paxData['first_name'],
                'family_name'          => $paxData['family_name'],
                'birth_date'           => $paxData['birth_date'],
                'gender'               => $paxData['gender'],
                'mofa_status'          => $paxData['mofa_status'] ?? '0',
                'visa_pipeline_status' => 'draft',
            ]);
            $created++;
        }

        return Reply::successWithData(
            "{$created} passengers imported" . ($skipped > 0 ? ", {$skipped} skipped (duplicates)" : '') . '.',
            ['redirectUrl' => route('bookings.show', $request->booking_group_id)]
        );
    }

}

