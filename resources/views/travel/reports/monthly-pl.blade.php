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
            <h1><i class="fa fa-calendar-alt mr-2 text-primary"></i>Monthly Profit & Loss Breakdown</h1>
            <p class="text-muted mb-0">Monthly Financial Distribution for Calendar Year {{ $year }}</p>
        </div>
        <div>
            <button onclick="window.print()" class="btn btn-sm btn-outline-dark"><i class="fa fa-print mr-1"></i>Print</button>
        </div>
    </div>

    <div class="rpt-filter">
        <form method="GET" action="{{ route('travel-reports.monthly-pl') }}" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label text-muted font-weight-bold" style="font-size:12px;">Financial Year</label>
                <select name="year" class="form-control form-control-sm font-mono">
                    @for($y = now()->year + 1; $y >= now()->year - 4; $y--)
                        <option value="{{ $y }}" @selected($y == $year)>{{ $y }}</option>
                    @endfor
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-sm btn-primary"><i class="fa fa-filter mr-1"></i>View Year</button>
            </div>
        </form>
    </div>

    @php
        $months = [
            1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
            5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
            9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
        ];
        $monthData = [];
        foreach(range(1, 12) as $m) {
            $monthData[$m] = ['income' => 0.0, 'expense' => 0.0, 'cogs' => 0.0];
        }
        foreach($monthly as $row) {
            $m = (int)$row->month;
            if (isset($monthData[$m])) {
                $monthData[$m][$row->type] = (float)$row->net;
            }
        }
    @endphp

    <div class="table-responsive">
        <table class="rpt-table">
            <thead>
                <tr>
                    <th>Month</th>
                    <th class="text-right">Revenue / Income</th>
                    <th class="text-right">Direct Costs (COGS)</th>
                    <th class="text-right">Operating Expenses</th>
                    <th class="text-right">Net Margin</th>
                </tr>
            </thead>
            <tbody>
                @php $totInc = 0; $totCogs = 0; $totExp = 0; $totNet = 0; @endphp
                @foreach($months as $num => $name)
                @php
                    $inc = $monthData[$num]['income'];
                    $cogs = abs($monthData[$num]['cogs']);
                    $exp = abs($monthData[$num]['expense']);
                    $net = $inc - $cogs - $exp;
                    $totInc += $inc; $totCogs += $cogs; $totExp += $exp; $totNet += $net;
                @endphp
                <tr>
                    <td class="font-weight-bold">{{ $name }}</td>
                    <td class="text-right font-mono text-success">{{ number_format($inc, 2) }}</td>
                    <td class="text-right font-mono text-warning">{{ number_format($cogs, 2) }}</td>
                    <td class="text-right font-mono text-danger">{{ number_format($exp, 2) }}</td>
                    <td class="text-right font-mono font-weight-bold {{ $net >= 0 ? 'text-success' : 'text-danger' }}">
                        {{ number_format($net, 2) }}
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr style="background:#fafbfe; font-weight:700; border-top:2px solid var(--ink);">
                    <td>TOTAL FOR YEAR {{ $year }}</td>
                    <td class="text-right font-mono text-success">{{ number_format($totInc, 2) }}</td>
                    <td class="text-right font-mono text-warning">{{ number_format($totCogs, 2) }}</td>
                    <td class="text-right font-mono text-danger">{{ number_format($totExp, 2) }}</td>
                    <td class="text-right font-mono {{ $totNet >= 0 ? 'text-success' : 'text-danger' }}">
                        {{ number_format($totNet, 2) }}
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
@endsection