<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use App\Events\VisaStatusChangedEvent;
use App\Models\BookingGroup;
use App\Models\Passenger;
use App\Models\VisaLog;
use App\Notifications\VisaStatusChangedSms;
use App\Services\TravelSmsService;
use Illuminate\Http\Request;

class VisaPipelineController extends AccountBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.visaPipeline';

        $this->middleware(function ($request, $next) {
            abort_403(!in_array('bookings', $this->user->modules));
            return $next($request);
        });
    }

    public function index(Request $request)
    {
        $viewPermission = user()->permission('view_booking');
        abort_403(!in_array($viewPermission, ['all', 'added', 'owned', 'both']));

        $this->bookingGroups     = BookingGroup::orderBy('group_name')->get();
        $this->selectedBookingId = $request->booking_group_id;
        $this->selectedBooking   = $this->selectedBookingId
            ? BookingGroup::find($this->selectedBookingId)
            : null;

        // Load passengers — filter by booking if selected, else load all
        $query = Passenger::with(['bookingGroup', 'visaStatusUpdatedBy'])
            ->when($this->selectedBookingId, fn($q) => $q->where('booking_group_id', $this->selectedBookingId));

        $this->passengers = $query->get();

        $this->columns = Passenger::VISA_STATUSES;

        return view('travel.visa-pipeline.index', $this->data);
    }

    /**
     * Move a passenger to a new visa pipeline status.
     */
    public function move(Request $request)
    {
        $editPermission = user()->permission('edit_booking');
        abort_403(!in_array($editPermission, ['all', 'added']));

        $request->validate([
            'passenger_id'      => 'required|exists:passengers,id',
            'new_status'        => 'required|in:draft,sent_to_embassy,mofa_received,issued,rejected',
            'mofa_ref'          => 'nullable|string|max:100',
            'rejection_reason'  => 'nullable|string|max:1000',
        ]);

        $passenger = Passenger::findOrFail($request->passenger_id);
        $fromStatus = $passenger->visa_pipeline_status;

        $passenger->visa_pipeline_status    = $request->new_status;
        $passenger->visa_status_updated_by  = user()->id;
        $passenger->visa_status_updated_at  = now();

        if ($request->filled('mofa_ref')) {
            $passenger->visa_mofa_ref = $request->mofa_ref;
        }

        if ($request->new_status === 'rejected' && $request->filled('rejection_reason')) {
            $passenger->visa_rejection_reason = $request->rejection_reason;
        }

        $passenger->save();

        // Audit trail — every status change must be recorded.
        VisaLog::create([
            'company_id'   => $passenger->company_id,
            'passenger_id' => $passenger->id,
            'from_status'  => $fromStatus,
            'to_status'    => $passenger->visa_pipeline_status,
            'changed_by'   => user()->id,
            'remarks'      => $request->rejection_reason
                ?? ($request->mofa_ref ? "MoFA ref: {$request->mofa_ref}" : null),
        ]);

        // Real-time broadcast — visa kanban and ops dashboards update instantly.
        event(new VisaStatusChangedEvent($passenger, $fromStatus));

        // SMS notification — only if passenger has a phone number on file.
        if (!empty($passenger->mobile_no)) {
            try {
                $sms = app(TravelSmsService::class);
                $sms->notifyVisaStatusChanged($passenger, $passenger->visa_pipeline_status);
            } catch (\Throwable $e) {
                // SMS failure must never block the main flow.
            }
        }

        return Reply::successWithData(__('messages.updateSuccess'), [
            'passenger_id' => $passenger->id,
            'new_status'   => $passenger->visa_pipeline_status,
        ]);
    }

    /**
     * Set/update a MoFA reference number for a passenger.
     */
    public function setMofaRef(Request $request)
    {
        $editPermission = user()->permission('edit_booking');
        abort_403(!in_array($editPermission, ['all', 'added']));

        $request->validate([
            'passenger_id' => 'required|exists:passengers,id',
            'mofa_ref'     => 'required|string|max:100',
        ]);

        $passenger = Passenger::findOrFail($request->passenger_id);
        $passenger->visa_mofa_ref           = $request->mofa_ref;
        $passenger->visa_status_updated_by  = user()->id;
        $passenger->visa_status_updated_at  = now();
        $passenger->save();

        // MoFA reference changes also get logged for the audit trail.
        VisaLog::create([
            'company_id'   => $passenger->company_id,
            'passenger_id' => $passenger->id,
            'from_status'  => $passenger->visa_pipeline_status,
            'to_status'    => $passenger->visa_pipeline_status,
            'changed_by'   => user()->id,
            'remarks'      => "MoFA ref set: {$request->mofa_ref}",
        ]);

        return Reply::success(__('messages.updateSuccess'));
    }
}
