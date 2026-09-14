@extends('layouts.app')

@push('css')
<style>
.osi-box { font-family: 'Inter', sans-serif; }
.osi-table { width:100%; border-collapse:collapse; background:#fff; border:1px solid #e3e5f0; border-radius:8px; }
.osi-table th { background:#fafbfe; border-bottom:2px solid #e3e5f0; padding:12px 16px; font-size:12px; text-transform:uppercase; color:#7d8299; font-weight:600; }
.osi-table td { padding:12px 16px; border-bottom:1px solid #e3e5f0; font-size:13px; vertical-align:middle; }
.font-mono { font-family: 'IBM Plex Mono', monospace; }
</style>
@endpush

@section('content')
<div class="content-wrapper osi-box">
    <div class="d-flex justify-content-between align-items-center mb-4 bg-white p-3 rounded border">
        <div>
            <h4 class="mb-0 font-weight-bold"><i class="fa fa-file-invoice mr-2 text-primary"></i>Other Service Invoices</h4>
            <p class="text-muted mb-0 small">Specialized service billings with real-time double-entry GL posting</p>
        </div>
        <div>
            <a href="{{ route('other-service-invoices.create') }}" class="btn btn-primary btn-sm">
                <i class="fa fa-plus mr-1"></i>Create Service Invoice
            </a>
        </div>
    </div>

    <div class="table-responsive bg-white rounded border">
        <table class="osi-table">
            <thead>
                <tr>
                    <th>Invoice #</th>
                    <th>Issue Date</th>
                    <th>Customer</th>
                    <th>Service Type</th>
                    <th>Amount</th>
                    <th>Total</th>
                    <th>JV Ref</th>
                    <th>Status</th>
                    <th class="text-right">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($invoices as $inv)
                <tr>
                    <td class="font-mono font-weight-bold">
                        <a href="{{ route('other-service-invoices.show', $inv->id) }}">{{ $inv->invoice_number }}</a>
                    </td>
                    <td class="font-mono">{{ $inv->issue_date ? $inv->issue_date->format('d M Y') : '--' }}</td>
                    <td>{{ $inv->customer ? $inv->customer->name : 'Walk-in Customer' }}</td>
                    <td><span class="badge badge-light border text-uppercase">{{ $inv->service_type }}</span></td>
                    <td class="font-mono">{{ currency_format($inv->amount, company()->currency_id) }}</td>
                    <td class="font-mono font-weight-bold">{{ currency_format($inv->total_amount, company()->currency_id) }}</td>
                    <td class="font-mono">
                        @if($inv->journal_voucher_id)
                            <a href="{{ route('journal-vouchers.show', $inv->journal_voucher_id) }}" class="badge badge-info" target="_blank">
                                JV-{{ $inv->journal_voucher_id }}
                            </a>
                        @else
                            <span class="text-muted">--</span>
                        @endif
                    </td>
                    <td>
                        <span class="badge badge-{{ $inv->status == 'paid' ? 'success' : 'warning' }}">
                            {{ strtoupper($inv->status) }}
                        </span>
                    </td>
                    <td class="text-right">
                        <a href="{{ route('other-service-invoices.show', $inv->id) }}" class="btn btn-xs btn-outline-primary mr-1" title="View"><i class="fa fa-eye"></i></a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="text-center py-4 text-muted">
                        No service invoices recorded yet. Click "Create Service Invoice" to record one.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">
        {{ $invoices->links() }}
    </div>
</div>
@endsection
