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
.score-badge { background:#eef1ff; color:#4f6ef7; padding:4px 10px; border-radius:12px; font-size:12px; font-weight:700; font-family:'IBM Plex Mono',monospace; }
</style>
@endpush

@section('content')
<div class="content-wrapper rpt-box">
    <div class="rpt-mast">
        <div>
            <h1><i class="fa fa-user-check mr-2 text-primary"></i>Employee Efficiency Report (Travel Operations)</h1>
            <p class="text-muted mb-0">Bookings Created, Vouchers Issued, Visa Status Transitions, and Attributable Revenue</p>
        </div>
        <div>
            <button onclick="window.print()" class="btn btn-sm btn-outline-dark"><i class="fa fa-print mr-1"></i>Print</button>
        </div>
    </div>

    <div class="rpt-filter">
        <form method="GET" action="{{ route('travel-reports.employee-efficiency') }}" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label text-muted font-weight-bold" style="font-size:12px;">Activity From</label>
                <input type="date" name="from_date" class="form-control form-control-sm font-mono" value="{{ request('from_date', $fromDate) }}">
            </div>
            <div class="col-md-3">
                <label class="form-label text-muted font-weight-bold" style="font-size:12px;">Activity To</label>
                <input type="date" name="to_date" class="form-control form-control-sm font-mono" value="{{ request('to_date', $toDate) }}">
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-sm btn-primary"><i class="fa fa-filter mr-1"></i>Filter</button>
                <a href="{{ route('travel-reports.employee-efficiency') }}" class="btn btn-sm btn-light border ml-1"><i class="fa fa-times mr-1"></i>Reset</a>
            </div>
        </form>
    </div>

    <div class="row">
        <div class="col-md-3">
            <div class="kpi-card">
                <div class="kpi-title">Bookings Created</div>
                <div class="kpi-val text-primary">{{ $totalBookings }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="kpi-card">
                <div class="kpi-title">Vouchers Issued</div>
                <div class="kpi-val text-info">{{ $totalVouchers }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="kpi-card">
                <div class="kpi-title">Visas Issued (MoFA)</div>
                <div class="kpi-val text-success">{{ $totalVisas }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="kpi-card">
                <div class="kpi-title">Attributable Revenue</div>
                <div class="kpi-val text-success">{{ number_format($totalRevenue, 2) }}</div>
            </div>
        </div>
    </div>

    <div class="table-responsive">
        <table class="rpt-table">
            <thead>
                <tr>
                    <th>Rank</th>
                    <th>Employee</th>
                    <th>Department / Designation</th>
                    <th class="text-center">Bookings Created</th>
                    <th class="text-center">Vouchers Issued</th>
                    <th class="text-center">Visa Cases Issued</th>
                    <th class="text-center">Total Operational Actions</th>
                    <th class="text-right">Attributable Revenue</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $idx => $r)
                <tr>
                    <td class="font-mono text-muted text-center font-weight-bold">#{{ $idx + 1 }}</td>
                    <td>
                        <div class="font-weight-bold">{{ $r->name }}</div>
                        <small class="text-muted">{{ $r->email }}</small>
                    </td>
                    <td>
                        <div>{{ $r->department }}</div>
                        <small class="text-muted">{{ $r->designation }}</small>
                    </td>
                    <td class="text-center font-mono font-weight-bold text-primary">{{ $r->bookings_count }}</td>
                    <td class="text-center font-mono font-weight-bold text-info">{{ $r->vouchers_count }}</td>
                    <td class="text-center font-mono font-weight-bold text-success">{{ $r->visas_count }}</td>
                    <td class="text-center">
                        <span class="score-badge">{{ $r->total_actions }} actions</span>
                    </td>
                    <td class="text-right font-mono font-weight-bold text-success">{{ number_format($r->revenue, 2) }}</td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center text-muted py-4">No employee activity records found for this period.</td></tr>
                @endforelse
            </tbody>
            @if(count($rows) > 0)
            <tfoot>
                <tr style="background:#fafbfe; font-weight:700;">
                    <td colspan="3" class="text-right text-uppercase">Summary Totals:</td>
                    <td class="text-center font-mono text-primary">{{ $totalBookings }}</td>
                    <td class="text-center font-mono text-info">{{ $totalVouchers }}</td>
                    <td class="text-center font-mono text-success">{{ $totalVisas }}</td>
                    <td class="text-center font-mono">{{ $totalBookings + $totalVouchers + $totalVisas }} actions</td>
                    <td class="text-right font-mono text-success">{{ number_format($totalRevenue, 2) }}</td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</div>
@endsection