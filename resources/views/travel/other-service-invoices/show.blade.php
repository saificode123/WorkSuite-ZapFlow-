@extends('layouts.app')

@push('css')
<style>
.font-mono { font-family: 'IBM Plex Mono', monospace; }
</style>
@endpush

@section('content')
<div class="content-wrapper">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <a href="{{ route('other-service-invoices.index') }}" class="btn btn-outline-secondary btn-sm"><i class="fa fa-arrow-left mr-1"></i>Back to List</a>
        <div>
            <button onclick="window.print()" class="btn btn-outline-dark btn-sm"><i class="fa fa-print mr-1"></i>Print</button>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded mb-4">
        <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center p-3">
            <div>
                <h4 class="mb-0 font-weight-bold font-mono text-primary">{{ $invoice->invoice_number }}</h4>
                <span class="badge badge-light border text-uppercase">{{ $invoice->service_type }}</span>
            </div>
            <div>
                <span class="badge badge-{{ $invoice->status == 'paid' ? 'success' : 'warning' }} p-2">
                    {{ strtoupper($invoice->status) }}
                </span>
            </div>
        </div>

        <div class="card-body p-4">
            <div class="row mb-4">
                <div class="col-md-6">
                    <p class="text-muted mb-1 text-uppercase small font-weight-bold">Billed To:</p>
                    <h5 class="font-weight-bold mb-1">{{ $invoice->customer ? $invoice->customer->name : 'Walk-in Customer' }}</h5>
                    @if($invoice->customer)
                        <p class="text-muted mb-0 small">{{ $invoice->customer->email }}</p>
                        @if($invoice->customer->mobile)
                            <p class="text-muted mb-0 small">{{ $invoice->customer->mobile }}</p>
                        @endif
                    @endif
                </div>
                <div class="col-md-6 text-md-right">
                    <p class="mb-1"><strong>Issue Date:</strong> <span class="font-mono">{{ $invoice->issue_date ? $invoice->issue_date->format('d M Y') : '--' }}</span></p>
                    <p class="mb-1"><strong>Due Date:</strong> <span class="font-mono">{{ $invoice->due_date ? $invoice->due_date->format('d M Y') : '--' }}</span></p>
                    @if($invoice->journal_voucher_id)
                        <p class="mb-0">
                            <strong>Journal Voucher:</strong>
                            <a href="{{ route('journal-vouchers.show', $invoice->journal_voucher_id) }}" class="badge badge-info font-mono" target="_blank">
                                JV-{{ $invoice->journal_voucher_id }}
                            </a>
                        </p>
                    @endif
                </div>
            </div>

            <div class="table-responsive mb-4">
                <table class="table table-bordered">
                    <thead class="bg-light">
                        <tr>
                            <th>Description</th>
                            <th class="text-right" style="width: 160px;">Base Amount</th>
                            <th class="text-right" style="width: 140px;">Tax</th>
                            <th class="text-right" style="width: 160px;">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <strong>{{ ucwords(str_replace('_', ' ', $invoice->service_type)) }}</strong>
                                @if($invoice->description)
                                    <p class="text-muted mb-0 small">{{ $invoice->description }}</p>
                                @endif
                            </td>
                            <td class="text-right font-mono">{{ currency_format($invoice->amount, company()->currency_id) }}</td>
                            <td class="text-right font-mono">{{ currency_format($invoice->tax_amount, company()->currency_id) }}</td>
                            <td class="text-right font-mono font-weight-bold">{{ currency_format($invoice->total_amount, company()->currency_id) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            {{-- GL Posting Breakdown --}}
            @if($invoice->journalVoucher)
            <div class="border rounded p-3 bg-light mb-3">
                <h6 class="font-weight-bold mb-2"><i class="fa fa-book mr-1 text-info"></i>General Ledger Posting Audit (JV-{{ $invoice->journal_voucher_id }})</h6>
                <div class="table-responsive">
                    <table class="table table-sm table-white mb-0 bg-white border">
                        <thead>
                            <tr class="bg-light">
                                <th>Account Code &amp; Name</th>
                                <th>Type</th>
                                <th class="text-right">Debit</th>
                                <th class="text-right">Credit</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($invoice->journalVoucher->lines as $line)
                            <tr>
                                <td>[{{ $line->account->code ?? '--' }}] {{ $line->account->name ?? '--' }}</td>
                                <td><span class="badge badge-secondary">{{ strtoupper($line->account->type ?? '--') }}</span></td>
                                <td class="text-right font-mono">{{ $line->debit > 0 ? currency_format($line->debit, company()->currency_id) : '-' }}</td>
                                <td class="text-right font-mono">{{ $line->credit > 0 ? currency_format($line->credit, company()->currency_id) : '-' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

            @if($invoice->notes)
                <div class="mt-3">
                    <p class="text-muted mb-1 small font-weight-bold">Notes:</p>
                    <p class="small mb-0">{{ $invoice->notes }}</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
