@extends('layouts.app')

@push('css')
<style>
@import url('https://fonts.googleapis.com/css2?family=Source+Serif+4:opsz,wght@8..60,500;8..60,600;8..60,700&family=Inter:wght@400;500;600&family=IBM+Plex+Mono:wght@400;500;600&display=swap');

.pl-report {
    --ink: #1b2036;
    --ink-soft: #454b63;
    --muted: #7d8299;
    --paper: #f5f6fa;
    --card: #ffffff;
    --hairline: #e3e5f0;
    --rule-gold: #a9822f;
    --positive: #1f7a52;
    --negative: #b43f3f;
    --c-revenue: #1f7a52;
    --c-cogs: #6d4fc7;
    --c-expense: #b43f3f;
    --c-other: #1c7fa0;
    color: var(--ink);
    font-family: 'Inter', -apple-system, sans-serif;
    background: var(--paper);
    padding: 4px 4px 8px;
    border-radius: 10px;
}

/* ---------- Masthead ---------- */
.pl-masthead {
    display: flex;
    align-items: baseline;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 8px;
    padding: 22px 26px 16px;
    border-bottom: 2px solid var(--ink);
    margin-bottom: 20px;
}
.pl-masthead h1 {
    font-family: 'Source Serif 4', Georgia, serif;
    font-weight: 600;
    font-size: 26px;
    letter-spacing: 0.2px;
    margin: 0;
    color: var(--ink);
}
.pl-masthead .pl-period {
    font-family: 'IBM Plex Mono', monospace;
    font-size: 12.5px;
    color: var(--muted);
    letter-spacing: 0.3px;
}

/* ---------- Filter toolbar ---------- */
.date-filter-bar {
    background: var(--card);
    border: 1px solid var(--hairline);
    border-radius: 8px;
    padding: 12px 18px;
    margin: 0 0 20px;
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 14px;
}
.date-filter-bar label {
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 0.6px;
    color: var(--muted);
    font-weight: 600;
}
.date-filter-bar .form-control {
    border: 1px solid var(--hairline);
    font-family: 'IBM Plex Mono', monospace;
    font-size: 13px;
    border-radius: 6px;
}
.date-filter-bar .form-control:focus {
    border-color: var(--ink-soft);
    box-shadow: 0 0 0 3px rgba(27,32,54,0.08);
}
.date-filter-bar .btn-primary {
    background: var(--ink);
    border-color: var(--ink);
    border-radius: 6px;
    font-weight: 500;
    letter-spacing: 0.2px;
}
.date-filter-bar .btn-primary:hover { background: #2d3357; border-color: #2d3357; }
.date-filter-bar .btn-outline-secondary {
    border-radius: 6px;
    border-color: var(--hairline);
    color: var(--ink-soft);
    font-weight: 500;
}
.date-filter-bar .btn-outline-secondary:hover {
    background: var(--ink);
    border-color: var(--ink);
    color: #fff;
}

/* ---------- Summary strip (ledger style, not cards) ---------- */
.pl-summary-strip {
    background: var(--card);
    border: 1px solid var(--hairline);
    border-radius: 10px;
    display: flex;
    flex-wrap: wrap;
    margin-bottom: 24px;
    overflow: hidden;
}
.pl-summary-cell {
    flex: 1 1 220px;
    padding: 16px 24px;
    border-right: 1px solid var(--hairline);
    position: relative;
}
.pl-summary-cell:last-child { border-right: none; }
.pl-summary-cell .label {
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 0.7px;
    color: var(--muted);
    font-weight: 600;
    margin-bottom: 6px;
}
.pl-summary-cell .value {
    font-family: 'IBM Plex Mono', monospace;
    font-size: 22px;
    font-weight: 600;
    font-variant-numeric: tabular-nums;
}
.pl-summary-cell.is-net { background: var(--ink); }
.pl-summary-cell.is-net .label { color: rgba(255,255,255,0.55); }
.pl-summary-cell.is-net .value { color: #fff; }
.pl-summary-cell.is-net .value.negative { color: #ff9d9d; }
.pl-summary-cell.is-net .value.positive { color: #6fe3b4; }
.value.positive { color: var(--positive); }
.value.negative { color: var(--negative); }

/* ---------- Sections ---------- */
.pl-section { margin-bottom: 18px; background: var(--card); border: 1px solid var(--hairline); border-radius: 10px; overflow: hidden; }
.pl-section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 20px;
    border-bottom: 1px solid var(--hairline);
    border-left: 4px solid var(--bar-color, var(--ink));
}
.pl-section-header .pl-title {
    font-family: 'Source Serif 4', Georgia, serif;
    font-weight: 600;
    font-size: 15px;
    color: var(--ink);
}
.pl-section-header .pl-amount {
    font-family: 'IBM Plex Mono', monospace;
    font-size: 14px;
    font-weight: 600;
    color: var(--ink-soft);
}
.pl-section--revenue .pl-section-header { --bar-color: var(--c-revenue); }
.pl-section--cogs .pl-section-header { --bar-color: var(--c-cogs); }
.pl-section--expenses .pl-section-header { --bar-color: var(--c-expense); }
.pl-section--other .pl-section-header { --bar-color: var(--c-other); }

.pl-table { width: 100%; border-collapse: collapse; }
.pl-table td { padding: 9px 20px; font-size: 13.5px; border-bottom: 1px solid var(--hairline); color: var(--ink-soft); }
.pl-table td:last-child {
    text-align: right;
    font-family: 'IBM Plex Mono', monospace;
    font-weight: 500;
    font-variant-numeric: tabular-nums;
    color: var(--ink);
}
.pl-table tr:hover td { background: #fafbff; }
.pl-table tr:last-of-type td { border-bottom: none; }

.pl-total-row td {
    font-weight: 700;
    background: var(--paper);
    border-top: 1px solid var(--hairline) !important;
    border-bottom: none !important;
}

/* ---------- Ledger totals (double rule = classic accounting sign-off) ---------- */
.pl-net-table { margin-top: 10px; }
.pl-net-row td {
    font-weight: 700;
    font-size: 14.5px;
    padding: 14px 20px;
    border-top: 3px double var(--ink);
    background: transparent;
    color: var(--ink);
}
.pl-net-row td:last-child {
    font-family: 'IBM Plex Mono', monospace;
    font-variant-numeric: tabular-nums;
}
.pl-net-row--final td {
    background: var(--ink);
    color: #fff;
    border-top: 3px double var(--rule-gold);
    font-size: 16px;
}
.pl-net-row--final td:first-child { border-radius: 8px 0 0 8px; }
.pl-net-row--final td:last-child { border-radius: 0 8px 8px 0; font-size: 18px; }
.pl-net-row--final .badge {
    font-family: 'Inter', sans-serif;
    font-size: 10px;
    letter-spacing: 0.4px;
    vertical-align: middle;
}

.pl-empty { text-align: center; color: var(--muted); padding: 22px 0 !important; font-style: italic; font-size: 13px; }
</style>
@endpush

@section('content')
<div class="content-wrapper">
<div class="pl-report">

    <div class="pl-masthead">
        <h1>Profit &amp; Loss Statement</h1>
        <span class="pl-period">{{ $fromDate }} &nbsp;&rarr;&nbsp; {{ $toDate }}</span>
    </div>

    {{-- Date Filter --}}
    <form method="GET" action="{{ route('travel-reports.profit-loss') }}" id="pl-filter-form">
        <div class="date-filter-bar">
            <div class="d-flex align-items-center gap-2">
                <label class="mb-0">@lang('app.from')</label>
                <input type="date" name="from_date" value="{{ $fromDate }}" class="form-control form-control-sm" style="width:150px">
            </div>
            <div class="d-flex align-items-center gap-2">
                <label class="mb-0">@lang('app.to')</label>
                <input type="date" name="to_date" value="{{ $toDate }}" class="form-control form-control-sm" style="width:150px">
            </div>
            <button type="submit" class="btn btn-primary btn-sm">
                <i class="fa fa-filter"></i> @lang('app.filter')
            </button>
            <a href="{{ route('travel-reports.export', 'profit-loss') }}?from_date={{ $fromDate }}&to_date={{ $toDate }}"
               class="btn btn-outline-secondary btn-sm ml-auto">
                <i class="fa fa-file-excel"></i> @lang('app.export')
            </a>
        </div>
    </form>

    {{-- Summary KPIs --}}
    <div class="pl-summary-strip">
        <div class="pl-summary-cell">
            <div class="label">@lang('modules.report.totalRevenue')</div>
            <div class="value positive">{{ number_format($revenue->sum('net'), 2) }}</div>
        </div>
        <div class="pl-summary-cell">
            <div class="label">@lang('modules.report.grossProfit')</div>
            <div class="value {{ $grossProfit >= 0 ? 'positive' : 'negative' }}">{{ number_format($grossProfit, 2) }}</div>
        </div>
        <div class="pl-summary-cell">
            <div class="label">@lang('modules.report.totalExpenses')</div>
            <div class="value negative">{{ number_format($expenses->sum('net'), 2) }}</div>
        </div>
        <div class="pl-summary-cell is-net">
            <div class="label">@lang('modules.report.netProfit')</div>
            <div class="value {{ $netProfit >= 0 ? 'positive' : 'negative' }}">{{ number_format(abs($netProfit), 2) }} {{ $netProfit < 0 ? '(LOSS)' : '' }}</div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6">

            {{-- Revenue --}}
            <div class="pl-section pl-section--revenue">
                <div class="pl-section-header">
                    <span class="pl-title">@lang('modules.report.revenue')</span>
                    <span class="pl-amount">{{ number_format($revenue->sum('net'), 2) }}</span>
                </div>
                <table class="pl-table">
                    @foreach($revenue as $acc)
                    <tr>
                        <td>{{ $acc->code }} — {{ $acc->name }}</td>
                        <td>{{ number_format($acc->net, 2) }}</td>
                    </tr>
                    @endforeach
                    @if($revenue->isEmpty())
                    <tr><td colspan="2" class="pl-empty">@lang('modules.report.noData')</td></tr>
                    @endif
                    <tr class="pl-total-row">
                        <td>@lang('modules.report.totalRevenue')</td>
                        <td>{{ number_format($revenue->sum('net'), 2) }}</td>
                    </tr>
                </table>
            </div>

            {{-- COGS --}}
            <div class="pl-section pl-section--cogs">
                <div class="pl-section-header">
                    <span class="pl-title">@lang('modules.report.cogs')</span>
                    <span class="pl-amount">{{ number_format($cogs->sum('net'), 2) }}</span>
                </div>
                <table class="pl-table">
                    @foreach($cogs as $acc)
                    <tr>
                        <td>{{ $acc->code }} — {{ $acc->name }}</td>
                        <td>{{ number_format($acc->net, 2) }}</td>
                    </tr>
                    @endforeach
                    @if($cogs->isEmpty())
                    <tr><td colspan="2" class="pl-empty">@lang('modules.report.noData')</td></tr>
                    @endif
                    <tr class="pl-total-row">
                        <td>@lang('modules.report.totalCogs')</td>
                        <td>{{ number_format($cogs->sum('net'), 2) }}</td>
                    </tr>
                </table>
            </div>

            {{-- Gross Profit row --}}
            <table class="pl-table pl-net-table">
                <tr class="pl-net-row">
                    <td>@lang('modules.report.grossProfit')</td>
                    <td>{{ number_format($grossProfit, 2) }}</td>
                </tr>
            </table>

        </div>
        <div class="col-lg-6">

            {{-- Expenses --}}
            <div class="pl-section pl-section--expenses">
                <div class="pl-section-header">
                    <span class="pl-title">@lang('modules.report.expenses')</span>
                    <span class="pl-amount">{{ number_format($expenses->sum('net'), 2) }}</span>
                </div>
                <table class="pl-table">
                    @foreach($expenses as $acc)
                    <tr>
                        <td>{{ $acc->code }} — {{ $acc->name }}</td>
                        <td>{{ number_format(abs($acc->net), 2) }}</td>
                    </tr>
                    @endforeach
                    @if($expenses->isEmpty())
                    <tr><td colspan="2" class="pl-empty">@lang('modules.report.noData')</td></tr>
                    @endif
                    <tr class="pl-total-row">
                        <td>@lang('modules.report.totalExpenses')</td>
                        <td>{{ number_format($expenses->sum(fn($e) => abs($e->net)), 2) }}</td>
                    </tr>
                </table>
            </div>

            @if($otherIncome->isNotEmpty())
            {{-- Other Income --}}
            <div class="pl-section pl-section--other">
                <div class="pl-section-header">
                    <span class="pl-title">@lang('modules.report.otherIncome')</span>
                    <span class="pl-amount">{{ number_format($otherIncome->sum('net'), 2) }}</span>
                </div>
                <table class="pl-table">
                    @foreach($otherIncome as $acc)
                    <tr>
                        <td>{{ $acc->code }} — {{ $acc->name }}</td>
                        <td>{{ number_format($acc->net, 2) }}</td>
                    </tr>
                    @endforeach
                </table>
            </div>
            @endif

            {{-- Net Profit --}}
            <table class="pl-table pl-net-table">
                <tr class="pl-net-row pl-net-row--final">
                    <td>
                        @lang('modules.report.netProfit')
                        @if($netProfit < 0) <span class="badge bg-danger ml-2">LOSS</span> @endif
                    </td>
                    <td>{{ number_format(abs($netProfit), 2) }}</td>
                </tr>
            </table>

        </div>
    </div>

</div>
</div>
@endsection