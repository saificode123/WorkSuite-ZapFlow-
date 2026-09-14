@extends('layouts.app')

@push('css')
<style>
@import url('https://fonts.googleapis.com/css2?family=Source+Serif+4:opsz,wght@8..60,600;8..60,700&family=Inter:wght@400;500;600&family=IBM+Plex+Mono:wght@400;500;600&display=swap');
.rpt-box { --ink:#1b2036; --muted:#7d8299; --card:#ffffff; --line:#e3e5f0; font-family:'Inter',sans-serif; color:var(--ink); }
.rpt-mast { display:flex; justify-content:space-between; align-items:baseline; flex-wrap:wrap; gap:10px; padding:20px 24px; border-bottom:2px solid var(--ink); background:var(--card); border-radius:8px 8px 0 0; }
.rpt-mast h1 { font-family:'Source Serif 4',Georgia,serif; font-size:24px; font-weight:600; margin:0; }
.rpt-table { width:100%; border-collapse:collapse; background:var(--card); border:1px solid var(--line); border-radius:8px; margin-top:15px; }
.rpt-table th { background:#fafbfe; border-bottom:2px solid var(--line); padding:11px 14px; font-size:12px; text-transform:uppercase; color:var(--muted); font-weight:600; }
.rpt-table td { padding:11px 14px; border-bottom:1px solid var(--line); font-size:13px; }
.font-mono { font-family:'IBM Plex Mono',monospace; }
.badge-profit { background:#e7f8f1; color:#17a673; padding:3px 8px; border-radius:10px; font-size:11px; font-weight:600; }
.badge-loss { background:#fdece9; color:#e2493d; padding:3px 8px; border-radius:10px; font-size:11px; font-weight:600; }
</style>
@endpush

@section('content')
<div class="content-wrapper rpt-box">
    <div class="rpt-mast">
        <div>
            <h1><i class="fa fa-kaaba mr-2 text-primary"></i>Umrah-Wise Profit & Loss Report</h1>
            <p class="text-muted mb-0">Booking Group Revenue, Vendor Disbursements, and Gross Profit Margins</p>
        </div>
        <div>
            <button onclick="window.print()" class="btn btn-sm btn-outline-dark"><i class="fa fa-print mr-1"></i>Print</button>
        </div>
    </div>

    <div class="table-responsive">
        <table class="rpt-table">
            <thead>
                <tr>
                    <th>Group Ref / Name</th>
                    <th>Package</th>
                    <th>Departure &rarr; Return</th>
                    <th class="text-center">Pax</th>
                    <th class="text-right">Billed Revenue</th>
                    <th class="text-right">Disbursed Cost</th>
                    <th class="text-right">Gross Margin</th>
                    <th class="text-center">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($items as $row)
                @php
                    $b = $row['booking'];
                    $pl = $row['pl'];
                    $rev = is_object($pl) ? $pl->revenue : ($pl['revenue'] ?? 0);
                    $cost = is_object($pl) ? $pl->cost : ($pl['cost'] ?? 0);
                    $margin = is_object($pl) ? $pl->margin : ($pl['margin'] ?? 0);
                    $pct = $rev > 0 ? round(($margin / $rev) * 100, 1) : 0;
                @endphp
                <tr>
                    <td>
                        <div class="font-weight-bold">{{ $b->group_name }}</div>
                        <small class="font-mono text-muted">{{ $b->group_no ?: ('BG-' . $b->id) }}</small>
                    </td>
                    <td>{{ $b->package?->name ?? 'Custom Package' }}</td>
                    <td class="font-mono text-muted">
                        {{ $b->departure_date ? \Carbon\Carbon::parse($b->departure_date)->format('d M Y') : '--' }}
                        &rarr;
                        {{ $b->return_date ? \Carbon\Carbon::parse($b->return_date)->format('d M Y') : '--' }}
                    </td>
                    <td class="text-center font-mono font-weight-bold">{{ $b->passengers->count() }}</td>
                    <td class="text-right font-mono font-weight-bold text-success">{{ number_format($rev, 2) }}</td>
                    <td class="text-right font-mono font-weight-bold text-warning">{{ number_format($cost, 2) }}</td>
                    <td class="text-right font-mono font-weight-bold {{ $margin >= 0 ? 'text-success' : 'text-danger' }}">
                        {{ number_format($margin, 2) }} ({{ $pct }}%)
                    </td>
                    <td class="text-center">
                        <span class="{{ $margin >= 0 ? 'badge-profit' : 'badge-loss' }}">
                            {{ $margin >= 0 ? 'PROFIT' : 'DEFICIT' }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center text-muted py-4">No booking groups found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-3">{{ $paginator->links() }}</div>
</div>
@endsection