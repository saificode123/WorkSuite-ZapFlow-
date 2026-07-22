<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use App\Models\ChartOfAccount;
use App\Models\TravelPayment;
use App\Models\User;
use App\Services\DoubleEntryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * TravelPaymentController
 *
 * Handles both Receive Payment (inbound from agent/client) and
 * Make Payment (outbound to hotel/visa company/transporter).
 *
 * Every payment creates a balanced journal voucher via DoubleEntryService.
 */
class TravelPaymentController extends AccountBaseController
{
    public function __construct(private DoubleEntryService $de)
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.travelPayments';

        $this->middleware(function ($request, $next) {
            abort_403(!in_array('accounting', $this->user->modules));
            return $next($request);
        });
    }

    // ── Receive Payment ────────────────────────────────────────────────────────

    public function receiveIndex()
    {
        abort_403(user()->permission('view_travel_payment') === 'none');

        $this->payments = TravelPayment::where('payment_direction', 'receive')
            ->with(['partyUser', 'debitAccount', 'creditAccount', 'bookingGroup'])
            ->latest('payment_date')
            ->paginate(25);

        return view('travel.payments.receive-index', $this->data);
    }

    public function receiveCreate()
    {
        abort_403(!in_array(user()->permission('add_travel_payment'), ['all', 'added']));

        $this->accounts = ChartOfAccount::whereIn('type', ['asset', 'bank'])
            ->orderBy('name')
            ->get();
        $this->clients = User::where('login', 'client')
            ->where('company_id', company_id())
            ->orderBy('name')
            ->get();
        $this->view = 'travel.payments.ajax.receive-create';

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return view('travel.payments.receive-create', $this->data);
    }

    public function receiveStore(Request $request)
    {
        abort_403(!in_array(user()->permission('add_travel_payment'), ['all', 'added']));

        $validated = $request->validate([
            'debit_account_id'  => 'required|exists:chart_of_accounts,id',
            'credit_account_id' => 'required|exists:chart_of_accounts,id|different:debit_account_id',
            'amount'            => 'required|numeric|min:0.01',
            'currency_code'     => 'nullable|string|max:10',
            'exchange_rate'     => 'nullable|numeric|min:0',
            'payment_date'      => 'required|date',
            'payment_method'    => 'required|in:cash,bank_transfer,cheque,online',
            'reference_no'      => 'nullable|string|max:100',
            'cheque_no'         => 'nullable|string|max:100',
            'narration'         => 'nullable|string|max:500',
            'party_user_id'     => 'nullable|exists:users,id',
            'booking_group_id'  => 'nullable|exists:booking_groups,id',
        ]);

        return DB::transaction(function () use ($validated) {
            // Post double-entry journal voucher
            $jv = $this->de->postSimple(
                date: $validated['payment_date'],
                debitAccountId:  (int) $validated['debit_account_id'],
                creditAccountId: (int) $validated['credit_account_id'],
                amount:          (float) $validated['amount'],
                narration:       $validated['narration'] ?? 'Receive Payment',
                createdBy:       user()->id,
            );

            $baseAmount = (float) $validated['amount'] * (float) ($validated['exchange_rate'] ?? 1);

            TravelPayment::create(array_merge($validated, [
                'payment_direction'    => 'receive',
                'amount_base_currency' => $baseAmount,
                'journal_voucher_id'   => $jv->id,
                'status'               => 'posted',
                'added_by'             => user()->id,
            ]));

            return Reply::successWithData(
                __('messages.recordSaved'),
                ['redirectUrl' => route('travel-payments.receive.index')]
            );
        });
    }

    // ── Make Payment ───────────────────────────────────────────────────────────

    public function makeIndex()
    {
        abort_403(user()->permission('view_travel_payment') === 'none');

        $this->payments = TravelPayment::where('payment_direction', 'make')
            ->with(['partyUser', 'debitAccount', 'creditAccount', 'bookingGroup'])
            ->latest('payment_date')
            ->paginate(25);

        return view('travel.payments.make-index', $this->data);
    }

    public function makeCreate()
    {
        abort_403(!in_array(user()->permission('add_travel_payment'), ['all', 'added']));

        $this->accounts = ChartOfAccount::orderBy('name')->get();
        $this->view     = 'travel.payments.ajax.make-create';

        if (request()->ajax()) {
            return $this->returnAjax($this->view);
        }

        return view('travel.payments.make-create', $this->data);
    }

    public function makeStore(Request $request)
    {
        abort_403(!in_array(user()->permission('add_travel_payment'), ['all', 'added']));

        $validated = $request->validate([
            'debit_account_id'  => 'required|exists:chart_of_accounts,id',
            'credit_account_id' => 'required|exists:chart_of_accounts,id|different:debit_account_id',
            'amount'            => 'required|numeric|min:0.01',
            'currency_code'     => 'nullable|string|max:10',
            'exchange_rate'     => 'nullable|numeric|min:0',
            'payment_date'      => 'required|date',
            'payment_method'    => 'required|in:cash,bank_transfer,cheque,online',
            'reference_no'      => 'nullable|string|max:100',
            'cheque_no'         => 'nullable|string|max:100',
            'narration'         => 'nullable|string|max:500',
            'party_user_id'     => 'nullable|exists:users,id',
            'booking_group_id'  => 'nullable|exists:booking_groups,id',
        ]);

        return DB::transaction(function () use ($validated) {
            $jv = $this->de->postSimple(
                date: $validated['payment_date'],
                debitAccountId:  (int) $validated['debit_account_id'],
                creditAccountId: (int) $validated['credit_account_id'],
                amount:          (float) $validated['amount'],
                narration:       $validated['narration'] ?? 'Make Payment',
                createdBy:       user()->id,
            );

            $baseAmount = (float) $validated['amount'] * (float) ($validated['exchange_rate'] ?? 1);

            TravelPayment::create(array_merge($validated, [
                'payment_direction'    => 'make',
                'amount_base_currency' => $baseAmount,
                'journal_voucher_id'   => $jv->id,
                'status'               => 'posted',
                'added_by'             => user()->id,
            ]));

            return Reply::successWithData(
                __('messages.recordSaved'),
                ['redirectUrl' => route('travel-payments.make.index')]
            );
        });
    }

    /**
     * Cancel a travel payment (reverses the journal voucher).
     */
    public function cancel(Request $request, int $id)
    {
        abort_403(!in_array(user()->permission('edit_travel_payment'), ['all', 'added']));

        return DB::transaction(function () use ($id) {
            $payment = TravelPayment::where('status', 'posted')->findOrFail($id);

            // Post reversal JV
            $this->de->postSimple(
                date: now()->toDateString(),
                debitAccountId:  (int) $payment->credit_account_id,  // reversed
                creditAccountId: (int) $payment->debit_account_id,
                amount:          (float) $payment->amount,
                narration:       'REVERSAL of Payment #' . $payment->id,
                createdBy:       user()->id,
            );

            $payment->update(['status' => 'cancelled']);

            return Reply::successWithData(
                __('messages.updateSuccess'),
                ['redirectUrl' => url()->previous()]
            );
        });
    }
}
