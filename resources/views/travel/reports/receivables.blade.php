@extends('layouts.app')

@push('css')
<style>
@import url('https://fonts.googleapis.com/css2?family=Source+Serif+4:opsz,wght@8..60,600;8..60,700&family=Inter:wght@400;500;600&family=IBM+Plex+Mono:wght@400;500;600&display=swap');
.rpt-box { --ink:#1b2036; --muted:#7d8299; --card:#ffffff; --line:#e3e5f0; font-family:'Inter',sans-serif; color:var(--ink); }
.rpt-mast { display:flex; justify-content:space-between; align-items:baseline; flex-wrap:wrap; gap:10px; padding:20px 24px; border-bottom:2px solid var(--ink); background:var(--card); border-radius:8px 8px 0 0; }
.rpt-mast h1 { font-family:'Source Serif 4',Georgia,serif; font-size:24px; font-weight:600; margin:0; }
.rpt-filter { background:var(--card); border:1px solid var(--line); border-radius:8px; padding:14px 18px; margin:15px 0; }
.rpt-table { width:100%; border-collapse:collapse; background:var(--card); border:1px solid var(--line); border-radius:8px; }
.rpt-table th { background:#fafbfe; border-bottom:2px solid var(--line); padding:11px 14px; font-size:12px; text-transform:uppercase; color:var(--muted); font-weight:600; }
.rpt-table td { padding:11px 14px; border-bottom:1px solid var(--line); font-size:13px; }
.font-mono { font-family:'IBM Plex Mono',monospace; }
.badge-posted { background:#e7f8f1; color:#17a673; padding:3px 8px; border-radius:10px; font-size:11px; font-weight:600; }
.badge-draft { background:#fef4e2; color:#e8960c; padding:3px 8px; border-radius:10px; font-size:11px; font-weight:600; }
</style>
@endpush

@section('content')
<div class="content-wrapper rpt-box">
    <div class="rpt-mast">
        <div>
            <h1><i class="fa fa-hand-holding-usd mr-2 text-primary"></i>Receivables Report</h1>
            <p class="text-muted mb-0">Customer & B2B Agent Accounts Receivable Inflows</p>
        </div>
        <div>
            <button onclick="window.print()" class="btn btn-sm btn-outline-dark"><i class="fa fa-print mr-1"></i>Print</button>
        </div>
    </div>

    <div class="rpt-filter">
        <form method="GET" action="{{ route('travel-reports.receivables') }}" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label text-muted font-weight-bold" style="font-size:12px;">From Date</label>
                <input type="date" name="from_date" class="form-control form-control-sm font-mono" value="{{ request('from_date') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label text-muted font-weight-bold" style="font-size:12px;">To Date</label>
                <input type="date" name="to_date" class="form-control form-control-sm font-mono" value="{{ request('to_date') }}">
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-sm btn-primary"><i class="fa fa-filter mr-1"></i>Filter</button>
                <a href="{{ route('travel-reports.receivables') }}" class="btn btn-sm btn-light border ml-1"><i class="fa fa-times mr-1"></i>Reset</a>
            </div>
        </form>
    </div>

    <div class="table-responsive">
        <table class="rpt-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Date</th>
                    <th>Client / Payer</th>
                    <th>Booking Group</th>
                    <th>Method</th>
                    <th>Reference</th>
                    <th>Status</th>
                    <th class="text-right">Amount</th>
                    <th class="text-right">Base Amount</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payments as $idx => $p)
                <tr>
                    <td class="font-mono text-muted">{{ $payments->firstItem() + $idx }}</td>
                    <td class="font-mono">{{ $p->payment_date ? \Carbon\Carbon::parse($p->payment_date)->format('Y-m-d') : '--' }}</td>
                    <td class="font-weight-bold">{{ $p->partyUser?->name ?? $p->received_from ?? 'Direct Client' }}</td>
                    <td>{{ $p->bookingGroup?->group_name ?? '--' }}</td>
                    <td class="text-capitalize">{{ $p->payment_method }}</td>
                    <td class="font-mono text-muted">{{ $p->reference_no ?: '--' }}</td>
                    <td><span class="badge-{{ $p->status }}">{{ $p->status }}</span></td>
                    <td class="text-right font-mono font-weight-bold">{{ number_format($p->amount, 2) }} {{ $p->currency_code }}</td>
                    <td class="text-right font-mono font-weight-bold text-success">{{ number_format($p->amount_base_currency, 2) }}</td>
                </tr>
                @empty
                <tr><td colspan="9" class="text-center text-muted py-4">No receivable records found for this period.</td></tr>
                @endforelse
            </tbody>
            @if($payments->isNotEmpty())
            <tfoot>
                <tr style="background:#fafbfe; font-weight:700;">
                    <td colspan="7" class="text-right text-uppercase">Page Total:</td>
                    <td class="text-right font-mono text-primary">{{ number_format($payments->sum('amount'), 2) }}</td>
                    <td class="text-right font-mono text-success">{{ number_format($payments->sum('amount_base_currency'), 2) }}</td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
    <div class="mt-3">{{ $payments->withQueryString()->links() }}</div>
</div>
@endsection