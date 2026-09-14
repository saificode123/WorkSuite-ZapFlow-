<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use App\Models\Voucher;
use App\Models\BookingGroup;
use App\Models\VoucherCharge;
use App\DataTables\Travel\VoucherDataTable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;

class VoucherController extends AccountBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.vouchers';

        $this->middleware(function ($request, $next) {
            abort_403(!in_array('vouchers', $this->user->modules));
            return $next($request);
        });
    }

    public function index(VoucherDataTable $dataTable)
    {
        $viewPermission = user()->permission('view_voucher');
        abort_403(!in_array($viewPermission, ['all', 'added', 'owned', 'both']));
        return $dataTable->render('travel.vouchers.index', $this->data);
    }

    public function create()
    {
        $this->addPermission = user()->permission('add_voucher');
        abort_403(!in_array($this->addPermission, ['all', 'added']));

        $this->bookingGroups = BookingGroup::orderBy('group_name')->get();
        $this->voucherTypes = Voucher::TYPES;
        $this->view = 'travel.vouchers.ajax.create';

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }
        return view('travel.vouchers.create', $this->data);
    }

    public function store(Request $request)
    {
        $this->addPermission = user()->permission('add_voucher');
        abort_403(!in_array($this->addPermission, ['all', 'added']));

        $request->validate([
            'booking_group_id' => 'required|exists:booking_groups,id',
            'type' => 'required|in:accommodation,transport,full',
            'date' => 'nullable|date',
        ]);

        $voucher = DB::transaction(function () use ($request) {
            $booking = BookingGroup::findOrFail($request->booking_group_id);
            $voucherNo = 'VCH-' . $booking->group_no . '-' . str_pad(Voucher::where('booking_group_id', $request->booking_group_id)->count() + 1, 3, '0', STR_PAD_LEFT);

            return Voucher::create([
                'company_id' => company()->id,
                'booking_group_id' => $request->booking_group_id,
                'voucher_number' => $voucherNo,
                'type' => $request->type,
                'date' => $request->date ?? now(),
                'status' => 'draft',
                'version' => 1,
                'added_by' => user()->id,
            ]);
        });

        return Reply::successWithData(__('messages.recordSaved'), [
            'redirectUrl' => route('vouchers.show', $voucher->id),
            'voucher_id' => $voucher->id,
        ]);
    }

    public function show($id)
    {
        $this->voucher = Voucher::with(['bookingGroup.passengers', 'charges', 'bookingGroup.package'])->findOrFail($id);
        $this->pageTitle = __('modules.vouchers.viewVoucher') . ' #' . $this->voucher->voucher_number;
        return view('travel.vouchers.show', $this->data);
    }

    public function edit($id)
    {
        $this->editPermission = user()->permission('edit_voucher');
        abort_403(!in_array($this->editPermission, ['all', 'added']));

        $this->voucher = Voucher::with('bookingGroup')->findOrFail($id);
        abort_if($this->voucher->isLocked(), 403, __('messages.voucherLocked'));

        $this->bookingGroups = BookingGroup::orderBy('group_name')->get();
        $this->voucherTypes = Voucher::TYPES;
        $this->view = 'travel.vouchers.ajax.edit';

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }
        return view('travel.vouchers.create', $this->data);
    }

    public function update(Request $request, $id)
    {
        $this->editPermission = user()->permission('edit_voucher');
        abort_403(!in_array($this->editPermission, ['all', 'added']));

        $voucher = Voucher::findOrFail($id);
        abort_if($voucher->isLocked(), 403, __('messages.voucherLocked'));

        $request->validate([
            'type' => 'required|in:accommodation,transport,full',
            'date' => 'nullable|date',
        ]);

        $voucher->update($request->only(['type', 'date']));

        return Reply::successWithData(__('messages.updateSuccess'), ['redirectUrl' => route('vouchers.index')]);
    }

    public function destroy($id)
    {
        $this->deletePermission = user()->permission('delete_voucher');
        abort_403(!in_array($this->deletePermission, ['all', 'added']));

        $voucher = Voucher::findOrFail($id);
        abort_if($voucher->isLocked(), 403, __('messages.voucherLocked'));

        $voucher->charges()->delete();
        $voucher->delete();

        return Reply::successWithData(__('messages.deleteSuccess'), ['redirectUrl' => route('vouchers.index')]);
    }

    public function issue(Request $request, $id)
    {
        abort_403(!in_array(user()->permission('edit_voucher'), ['all', 'added']));

        return DB::transaction(function () use ($request, $id) {
            $voucher = Voucher::findOrFail($id);
            abort_if($voucher->isLocked(), 403, __('messages.voucherLocked'));

            $voucher->update([
                'status' => 'issued',
                'locked_at' => now(),
                'locked_by' => user()->id,
            ]);

            $this->generateQrPayload($voucher);

            return Reply::successWithData(
                __('messages.voucherIssued'),
                ['redirectUrl' => route('vouchers.show', $voucher->id)]
            );
        });
    }

    public function lock(Request $request, $id)
    {
        abort_403(!in_array(user()->permission('edit_voucher'), ['all', 'added']));

        return DB::transaction(function () use ($request, $id) {
            $voucher = Voucher::findOrFail($id);
            abort_if($voucher->isLocked(), 403, __('messages.voucherLocked'));

            $voucher->update([
                'status' => 'locked',
                'locked_at' => now(),
                'locked_by' => user()->id,
            ]);

            return Reply::successWithData(
                __('messages.voucherLocked'),
                ['redirectUrl' => route('vouchers.show', $voucher->id)]
            );
        });
    }

    public function generatePdf($id)
    {
        $voucher = Voucher::with(['bookingGroup.passengers', 'charges', 'bookingGroup.package', 'bookingGroup.customer'])->findOrFail($id);

        $pdf = Pdf::loadView('travel.vouchers.pdf', [
            'voucher' => $voucher,
            'company' => company(),
        ]);

        $filename = 'voucher-' . $voucher->voucher_number . '.pdf';
        return $pdf->download($filename);
    }

    public function qrCode($id)
    {
        $voucher = Voucher::findOrFail($id);

        if (!$voucher->qr_payload) {
            $this->generateQrPayload($voucher);
        }

        $result = Builder::create()
            ->writer(new PngWriter())
            ->data($voucher->qr_payload)
            ->encoding(new Encoding('UTF-8'))
            ->errorCorrectionLevel(ErrorCorrectionLevel::Medium)
            ->size(300)
            ->margin(10)
            ->build();

        return response($result->getString(), 200, ['Content-Type' => 'image/png']);
    }

    private function generateQrPayload(Voucher $voucher): void
    {
        $payload = json_encode([
            'v' => $voucher->voucher_number,
            'b' => $voucher->booking_group_id,
            's' => $voucher->status,
            't' => $voucher->type,
            'h' => hash_hmac('sha256', $voucher->id . '|' . $voucher->version, config('app.key')),
        ]);

        $voucher->update(['qr_payload' => $payload]);
    }
}
