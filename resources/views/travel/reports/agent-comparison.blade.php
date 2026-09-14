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
.badge-profit { background:#e7f8f1; color:#17a673; padding:3px 8px; border-radius:10px; font-size:11px; font-weight:600; }
.badge-loss { background:#fdece9; color:#e2493d; padding:3px 8px; border-radius:10px; font-size:11px; font-weight:600; }
.kpi-card { background:var(--card); border:1px solid var(--line); border-radius:8px; padding:16px; margin-bottom:15px; }
.kpi-title { font-size:12px; font-weight:600; text-transform:uppercase; color:var(--muted); margin-bottom:6px; }
.kpi-val { font-family:'IBM Plex Mono',monospace; font-size:20px; font-weight:700; color:var(--ink); }
</style>
@endpush

@section('content')
<div class="content-wrapper rpt-box">
    <div class="rpt-mast">
        <div>
            <h1><i class="fa fa-users mr-2 text-primary"></i>Travel Agent Comparison Report</h1>
            <p class="text-muted mb-0">B2B Agent Booking Volume, Gross Margin Ranking, and Cancellation Rates</p>
        </div>
        <div>
            <button onclick="window.print()" class="btn btn-sm btn-outline-dark"><i class="fa fa-print mr-1"></i>Print</button>
        </div>
    </div>

    <div class="rpt-filter">
        <form method="GET" action="{{ route('travel-reports.agent-comparison') }}" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label text-muted font-weight-bold" style="font-size:12px;">Bookings From</label>
                <input type="date" name="from_date" class="form-control form-control-sm font-mono" value="{{ request('from_date', $fromDate) }}">
            </div>
            <div class="col-md-3">
                <label class="form-label text-muted font-weight-bold" style="font-size:12px;">Bookings To</label>
                <input type="date" name="to_date" class="form-control form-control-sm font-mono" value="{{ request('to_date', $toDate) }}">
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-sm btn-primary"><i class="fa fa-filter mr-1"></i>Filter</button>
                <a href="{{ route('travel-reports.agent-comparison') }}" class="btn btn-sm btn-light border ml-1"><i class="fa fa-times mr-1"></i>Reset</a>
            </div>
        </form>
    </div>

    <div class="row">
        <div class="col-md-3">
            <div class="kpi-card">
                <div class="kpi-title">Total Agents</div>
                <div class="kpi-val">{{ count($agents) }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="kpi-card">
                <div class="kpi-title">Total Bookings</div>
                <div class="kpi-val text-primary">{{ $totalBookings }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="kpi-card">
                <div class="kpi-title">Total Billed Revenue</div>
                <div class="kpi-val text-success">{{ number_format($totalRevenue, 2) }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="kpi-card">
                <div class="kpi-title">Net Gross Margin</div>
                <div class="kpi-val {{ $totalMargin >= 0 ? 'text-success' : 'text-danger' }}">{{ number_format($totalMargin, 2) }}</div>
            </div>
        </div>
    </div>

    <div class="table-responsive">
        <table class="rpt-table">
            <thead>
                <tr>
                    <th>Rank</th>
                    <th>Agent / Customer</th>
                    <th>Company / Agency</th>
                    <th class="text-center">Total Bookings</th>
                    <th class="text-center">Confirmed</th>
                    <th class="text-center">Cancelled</th>
                    <th class="text-center">Cancel Rate</th>
                    <th class="text-center">Total Pax</th>
                    <th class="text-right">Revenue</th>
                    <th class="text-right">Disbursed Cost</th>
                    <th class="text-right">Gross Margin</th>
                    <th class="text-center">Margin %</th>
                </tr>
            </thead>
            <tbody>
                @forelse($agents as $idx => $a)
                <tr>
                    <td class="font-mono text-muted text-center font-weight-bold">#{{ $idx + 1 }}</td>
                    <td>
                        <div class="font-weight-bold">{{ $a->name }}</div>
                        <small class="text-muted">{{ $a->email }}</small>
                    </td>
                    <td>{{ $a->company_name ?? '--' }}</td>
                    <td class="text-center font-mono font-weight-bold">{{ $a->total_bookings }}</td>
                    <td class="text-center font-mono text-success">{{ $a->confirmed_bookings }}</td>
                    <td class="text-center font-mono text-danger">{{ $a->cancelled_bookings }}</td>
                    <td class="text-center font-mono">
                        <span class="{{ $a->cancellation_rate > 20 ? 'badge-loss' : 'badge-profit' }}">
                            {{ $a->cancellation_rate }}%
                        </span>
                    </td>
                    <td class="text-center font-mono font-weight-bold">{{ $a->total_pax }}</td>
                    <td class="text-right font-mono font-weight-bold text-success">{{ number_format($a->total_revenue, 2) }}</td>
                    <td class="text-right font-mono font-weight-bold text-warning">{{ number_format($a->total_cost, 2) }}</td>
                    <td class="text-right font-mono font-weight-bold {{ $a->gross_margin >= 0 ? 'text-success' : 'text-danger' }}">
                        {{ number_format($a->gross_margin, 2) }}
                    </td>
                    <td class="text-center font-mono font-weight-bold {{ $a->margin_percent >= 0 ? 'text-success' : 'text-danger' }}">
                        {{ $a->margin_percent }}%
                    </td>
                </tr>
                @empty
                <tr><td colspan="12" class="text-center text-muted py-4">No travel agent bookings found for this period.</td></tr>
                @endforelse
            </tbody>
            @if(count($agents) > 0)
            <tfoot>
                <tr style="background:#fafbfe; font-weight:700;">
                    <td colspan="3" class="text-right text-uppercase">Summary Totals:</td>
                    <td class="text-center font-mono">{{ $totalBookings }}</td>
                    <td colspan="3"></td>
                    <td class="text-center font-mono">{{ $totalPax }}</td>
                    <td class="text-right font-mono text-success">{{ number_format($totalRevenue, 2) }}</td>
                    <td class="text-right font-mono text-warning">{{ number_format($totalCost, 2) }}</td>
                    <td class="text-right font-mono {{ $totalMargin >= 0 ? 'text-success' : 'text-danger' }}">{{ number_format($totalMargin, 2) }}</td>
                    <td class="text-center font-mono">{{ $totalRevenue > 0 ? round(($totalMargin / $totalRevenue) * 100, 1) : 0 }}%</td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</div>
@endsection