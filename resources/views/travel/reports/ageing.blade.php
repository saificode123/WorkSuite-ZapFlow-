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
</style>
@endpush

@section('content')
<div class="content-wrapper rpt-box">
    <div class="rpt-mast">
        <div>
            <h1><i class="fa fa-hourglass-half mr-2 text-primary"></i>Accounts Ageing Analysis</h1>
            <p class="text-muted mb-0">Aging distribution (0-30, 31-60, 61-90, 90+ days) as of {{ $asOfDate }}</p>
        </div>
        <div>
            <button onclick="window.print()" class="btn btn-sm btn-outline-dark"><i class="fa fa-print mr-1"></i>Print</button>
        </div>
    </div>

    <div class="rpt-filter">
        <form method="GET" action="{{ route('travel-reports.ageing') }}" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label text-muted font-weight-bold" style="font-size:12px;">Type</label>
                <select name="type" class="form-control form-control-sm">
                    <option value="receivable" @selected($type == 'receivable')>Receivables (Clients / Agents)</option>
                    <option value="payable" @selected($type == 'payable')>Payables (Vendors / Airlines)</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label text-muted font-weight-bold" style="font-size:12px;">As of Date</label>
                <input type="date" name="as_of_date" class="form-control form-control-sm font-mono" value="{{ $asOfDate }}">
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-sm btn-primary"><i class="fa fa-filter mr-1"></i>Filter</button>
            </div>
        </form>
    </div>

    <div class="table-responsive">
        <table class="rpt-table">
            <thead>
                <tr>
                    <th>Party Name</th>
                    <th class="text-right">Current (0-30 Days)</th>
                    <th class="text-right">31-60 Days</th>
                    <th class="text-right">61-90 Days</th>
                    <th class="text-right">90+ Days</th>
                    <th class="text-right">Total Outstanding</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $refDate = \Carbon\Carbon::parse($asOfDate);
                    $groups = collect($rows)->groupBy('name');
                    $tot0 = 0; $tot31 = 0; $tot61 = 0; $tot90 = 0; $totAll = 0;
                @endphp
                @forelse($groups as $partyName => $items)
                @php
                    $b0 = 0; $b31 = 0; $b61 = 0; $b90 = 0;
                    foreach($items as $it) {
                        $pDate = \Carbon\Carbon::parse($it->payment_date);
                        $diff = $pDate->diffInDays($refDate);
                        $amt = (float)$it->total;
                        if ($diff <= 30) $b0 += $amt;
                        elseif ($diff <= 60) $b31 += $amt;
                        elseif ($diff <= 90) $b61 += $amt;
                        else $b90 += $amt;
                    }
                    $partyTotal = $b0 + $b31 + $b61 + $b90;
                    $tot0 += $b0; $tot31 += $b31; $tot61 += $b61; $tot90 += $b90; $totAll += $partyTotal;
                @endphp
                <tr>
                    <td class="font-weight-bold">{{ $partyName }}</td>
                    <td class="text-right font-mono text-muted">{{ number_format($b0, 2) }}</td>
                    <td class="text-right font-mono text-muted">{{ number_format($b31, 2) }}</td>
                    <td class="text-right font-mono text-muted">{{ number_format($b61, 2) }}</td>
                    <td class="text-right font-mono text-muted">{{ number_format($b90, 2) }}</td>
                    <td class="text-right font-mono font-weight-bold text-primary">{{ number_format($partyTotal, 2) }}</td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-muted py-4">No ageing transactions recorded as of this date.</td></tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr style="background:#fafbfe; font-weight:700;">
                    <td class="text-uppercase">Total:</td>
                    <td class="text-right font-mono">{{ number_format($tot0, 2) }}</td>
                    <td class="text-right font-mono">{{ number_format($tot31, 2) }}</td>
                    <td class="text-right font-mono">{{ number_format($tot61, 2) }}</td>
                    <td class="text-right font-mono">{{ number_format($tot90, 2) }}</td>
                    <td class="text-right font-mono text-primary">{{ number_format($totAll, 2) }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
@endsection