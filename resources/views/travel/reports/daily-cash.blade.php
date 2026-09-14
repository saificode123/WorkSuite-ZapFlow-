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
.kpi-card { background:var(--card); border:1px solid var(--line); border-radius:8px; padding:16px; margin-bottom:15px; }
.kpi-title { font-size:12px; font-weight:600; text-transform:uppercase; color:var(--muted); margin-bottom:6px; }
.kpi-val { font-family:'IBM Plex Mono',monospace; font-size:20px; font-weight:700; color:var(--ink); }
.badge-cash { background:#e7f8f1; color:#17a673; padding:3px 8px; border-radius:10px; font-size:11px; font-weight:600; }
</style>
@endpush

@section('content')
<div class="content-wrapper rpt-box">
    <div class="rpt-mast">
        <div>
            <h1><i class="fa fa-money-bill-wave mr-2 text-primary"></i>Daily Cash Transaction Report</h1>
            <p class="text-muted mb-0">Daily Cash Receipt Aggregates with Opening &amp; Closing Running Balances</p>
        </div>
        <div>
            <button onclick="window.print()" class="btn btn-sm btn-outline-dark"><i class="fa fa-print mr-1"></i>Print</button>
        </div>
    </div>

    <div class="rpt-filter">
        <form method="GET" action="{{ route('travel-reports.daily-cash') }}" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label text-muted font-weight-bold" style="font-size:12px;">From Date</label>
                <input type="date" name="from_date" class="form-control form-control-sm font-mono" value="{{ request('from_date', $fromDate) }}">
            </div>
            <div class="col-md-3">
                <label class="form-label text-muted font-weight-bold" style="font-size:12px;">To Date</label>
                <input type="date" name="to_date" class="form-control form-control-sm font-mono" value="{{ request('to_date', $toDate) }}">
            </div>
            <div class="col-md-3">
                <label class="form-label text-muted font-weight-bold" style="font-size:12px;">Deposit Account</label>
                <select name="account_id" class="form-control form-control-sm">
                    <option value="">All Cash &amp; Bank Accounts</option>
                    @foreach($accounts as $acc)
                    <option value="{{ $acc->id }}" {{ request('account_id', $accountId) == $acc->id ? 'selected' : '' }}>
                        {{ $acc->code }} — {{ $acc->name }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-sm btn-primary"><i class="fa fa-filter mr-1"></i>Filter</button>
                <a href="{{ route('travel-reports.daily-cash') }}" class="btn btn-sm btn-light border ml-1"><i class="fa fa-times mr-1"></i>Reset</a>
            </div>
        </form>
    </div>

    <div class="row">
        <div class="col-md-3">
            <div class="kpi-card">
                <div class="kpi-title">Opening Balance (Before {{ \Carbon\Carbon::parse($fromDate)->format('d M Y') }})</div>
                <div class="kpi-val text-primary">{{ number_format($openingBalance, 2) }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="kpi-card">
                <div class="kpi-title">Total Cash Receipts</div>
                <div class="kpi-val text-success">{{ number_format($totalReceipts, 2) }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="kpi-card">
                <div class="kpi-title">Closing Running Balance</div>
                <div class="kpi-val text-success">{{ number_format($closingBalance, 2) }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="kpi-card">
                <div class="kpi-title">Total Receipts Count</div>
                <div class="kpi-val">{{ $totalCount }}</div>
            </div>
        </div>
    </div>

    <!-- Daily Running Balance Summary -->
    <h5 class="font-weight-bold mt-4 mb-2"><i class="fa fa-calendar-day mr-2 text-primary"></i>Daily Summary &amp; Running Totals</h5>
    <div class="table-responsive mb-4">
        <table class="rpt-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th class="text-center">Receipts Count</th>
                    <th class="text-right">Opening Balance</th>
                    <th class="text-right">Receipts Collected</th>
                    <th class="text-right">Closing Balance</th>
                </tr>
            </thead>
            <tbody>
                @forelse($days as $d)
                <tr>
                    <td class="font-mono font-weight-bold">
                        {{ \Carbon\Carbon::parse($d->date)->format('d M Y (D)') }}
                    </td>
                    <td class="text-center font-mono font-weight-bold text-primary">{{ $d->receipt_count }}</td>
                    <td class="text-right font-mono text-muted">{{ number_format($d->opening_balance, 2) }}</td>
                    <td class="text-right font-mono font-weight-bold text-success">+{{ number_format($d->daily_amount, 2) }}</td>
                    <td class="text-right font-mono font-weight-bold text-primary">{{ number_format($d->closing_balance, 2) }}</td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-center text-muted py-4">No cash receipt transactions recorded in this date range.</td></tr>
                @endforelse
            </tbody>
            @if(count($days) > 0)
            <tfoot>
                <tr style="background:#fafbfe; font-weight:700;">
                    <td>Period Total</td>
                    <td class="text-center font-mono">{{ $totalCount }}</td>
                    <td class="text-right font-mono text-muted">{{ number_format($openingBalance, 2) }}</td>
                    <td class="text-right font-mono text-success">+{{ number_format($totalReceipts, 2) }}</td>
                    <td class="text-right font-mono text-primary">{{ number_format($closingBalance, 2) }}</td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>

    <!-- Individual Receipts Breakdown -->
    @if(count($receipts) > 0)
    <h5 class="font-weight-bold mt-4 mb-2"><i class="fa fa-list mr-2 text-secondary"></i>Detailed Transaction Journal</h5>
    <div class="table-responsive">
        <table class="rpt-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Date</th>
                    <th>Ref / Receipt #</th>
                    <th>Account</th>
                    <th>Received From</th>
                    <th>Narration</th>
                    <th>Cashier / Added By</th>
                    <th class="text-right">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($receipts as $idx => $r)
                <tr>
                    <td class="font-mono text-muted">{{ $idx + 1 }}</td>
                    <td class="font-mono">{{ \Carbon\Carbon::parse($r->date)->format('d M Y') }}</td>
                    <td class="font-mono font-weight-bold text-primary">{{ $r->reference_no ?: ('CR-' . str_pad($r->id, 5, '0', STR_PAD_LEFT)) }}</td>
                    <td>
                        <div class="font-weight-bold">{{ $r->account_name ?? '--' }}</div>
                        <small class="font-mono text-muted">{{ $r->account_code }}</small>
                    </td>
                    <td class="font-weight-bold">{{ $r->received_from ?: '--' }}</td>
                    <td>{{ $r->narration ?: '--' }}</td>
                    <td>{{ $r->added_by_name ?? '--' }}</td>
                    <td class="text-right font-mono font-weight-bold text-success">{{ number_format($r->amount, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr style="background:#fafbfe; font-weight:700;">
                    <td colspan="7" class="text-right text-uppercase">Total Period Receipts:</td>
                    <td class="text-right font-mono text-success">{{ number_format($totalReceipts, 2) }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
    @endif
</div>
@endsection