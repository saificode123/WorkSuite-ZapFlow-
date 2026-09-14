<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use App\Models\ChartOfAccount;
use App\Models\OtherServiceInvoice;
use App\Models\User;
use App\Services\DoubleEntryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OtherServiceInvoiceController extends AccountBaseController
{
    public function __construct(private DoubleEntryService $de)
    {
        parent::__construct();
        $this->pageTitle = 'Other Service Invoices';

        $this->middleware(function ($request, $next) {
            abort_403(!in_array('accounts', $this->user->modules));
            return $next($request);
        });
    }

    public function index()
    {
        $viewPermission = user()->permission('view_invoices');
        abort_403(!in_array($viewPermission, ['all', 'added', 'owned', 'both']) && !in_array(user()->permission('view_journal_voucher'), ['all', 'added', 'owned', 'both']));

        $this->invoices = OtherServiceInvoice::with(['customer', 'debitAccount', 'creditAccount', 'journalVoucher'])
            ->where('company_id', company()->id)
            ->latest('id')
            ->paginate(15);

        return view('travel.other-service-invoices.index', $this->data);
    }

    public function create()
    {
        $addPermission = user()->permission('add_invoices');
        abort_403(!in_array($addPermission, ['all', 'added']) && !in_array(user()->permission('add_journal_voucher'), ['all', 'added']));

        $this->customers = User::allClients();
        $this->accounts = ChartOfAccount::where('company_id', company()->id)->orderBy('name')->get();
        $this->defaultDebitAccount = ChartOfAccount::where('company_id', company()->id)
            ->where('type', 'asset')
            ->first();
        $this->defaultCreditAccount = ChartOfAccount::where('company_id', company()->id)
            ->where('type', 'income')
            ->first();
        $this->nextInvoiceNumber = 'OSI-' . date('Ymd') . '-' . rand(100, 999);

        return view('travel.other-service-invoices.create', $this->data);
    }

    public function store(Request $request)
    {
        $addPermission = user()->permission('add_invoices');
        abort_403(!in_array($addPermission, ['all', 'added']) && !in_array(user()->permission('add_journal_voucher'), ['all', 'added']));

        $validated = $request->validate([
            'invoice_number'    => 'required|string|max:100',
            'issue_date'        => 'required|date',
            'due_date'          => 'nullable|date',
            'customer_id'       => 'nullable|exists:users,id',
            'service_type'      => 'required|string|max:100',
            'description'       => 'nullable|string|max:500',
            'amount'            => 'required|numeric|min:0.01',
            'tax_amount'        => 'nullable|numeric|min:0',
            'total_amount'      => 'required|numeric|min:0.01',
            'debit_account_id'  => 'required|exists:chart_of_accounts,id',
            'credit_account_id' => 'required|exists:chart_of_accounts,id|different:debit_account_id',
            'notes'             => 'nullable|string',
        ]);

        return DB::transaction(function () use ($validated) {
            // Post double-entry journal voucher via DoubleEntryService
            $jv = $this->de->postSimple(
                date: $validated['issue_date'],
                debitAccountId: (int) $validated['debit_account_id'],
                creditAccountId: (int) $validated['credit_account_id'],
                amount: (float) $validated['total_amount'],
                narration: 'Other Service Invoice: ' . $validated['invoice_number'] . ' (' . $validated['service_type'] . ')',
                createdBy: user()->id,
            );

            $invoice = OtherServiceInvoice::create(array_merge($validated, [
                'company_id'         => company()->id,
                'journal_voucher_id' => $jv->id,
                'status'             => 'pending',
                'added_by'           => user()->id,
            ]));

            // Audit log write
            \App\Models\AuditLog::create([
                'company_id'  => company()->id,
                'user_id'     => user()->id,
                'module'      => 'accounts',
                'action'      => 'create_other_service_invoice',
                'entity_type' => 'other_service_invoice',
                'entity_id'   => $invoice->id,
                'field'       => 'total_amount',
                'ip_address'  => request()->ip(),
                'user_agent'  => substr((string) request()->userAgent(), 0, 255),
            ]);

            return Reply::successWithData(
                __('messages.recordSaved'),
                ['redirectUrl' => route('other-service-invoices.index')]
            );
        });
    }

    public function show(int $id)
    {
        $viewPermission = user()->permission('view_invoices');
        abort_403(!in_array($viewPermission, ['all', 'added', 'owned', 'both']) && !in_array(user()->permission('view_journal_voucher'), ['all', 'added', 'owned', 'both']));

        $this->invoice = OtherServiceInvoice::with(['customer', 'debitAccount', 'creditAccount', 'journalVoucher.lines.account'])
            ->where('company_id', company()->id)
            ->findOrFail($id);

        return view('travel.other-service-invoices.show', $this->data);
    }

    public function destroy(int $id)
    {
        $deletePermission = user()->permission('delete_invoices');
        abort_403(!in_array($deletePermission, ['all', 'added']) && !in_array(user()->permission('delete_journal_voucher'), ['all', 'added']));

        $invoice = OtherServiceInvoice::where('company_id', company()->id)->findOrFail($id);
        $invoice->delete();

        \App\Models\AuditLog::create([
            'company_id'  => company()->id,
            'user_id'     => user()->id,
            'module'      => 'accounts',
            'action'      => 'delete_other_service_invoice',
            'entity_type' => 'other_service_invoice',
            'entity_id'   => $id,
            'field'       => 'id',
            'ip_address'  => request()->ip(),
            'user_agent'  => substr((string) request()->userAgent(), 0, 255),
        ]);

        return Reply::success(__('messages.deleteSuccess'));
    }
}
