@extends('layouts.app')

@push('css')
<style>
@import url('https://fonts.googleapis.com/css2?family=Source+Serif+4:opsz,wght@8..60,500;8..60,600;8..60,700&family=Inter:wght@400;500;600&family=IBM+Plex+Mono:wght@400;500;600&display=swap');

.tb-report {
    --ink: #1b2036;
    --ink-soft: #454b63;
    --muted: #7d8299;
    --paper: #f5f6fa;
    --card: #ffffff;
    --hairline: #e3e5f0;
    --rule-gold: #a9822f;
    color: var(--ink);
    font-family: 'Inter', -apple-system, sans-serif;
}

/* ---------- Masthead ---------- */
.tb-masthead {
    display: flex;
    align-items: baseline;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 10px;
    padding: 22px 26px 16px;
    border-bottom: 2px solid var(--ink);
    margin-bottom: 20px;
}
.tb-masthead h1 {
    font-family: 'Source Serif 4', Georgia, serif;
    font-weight: 600;
    font-size: 24px;
    letter-spacing: 0.2px;
    margin: 0 0 3px;
    color: var(--ink);
}
.tb-masthead .tb-period {
    font-family: 'IBM Plex Mono', monospace;
    font-size: 12.5px;
    color: var(--muted);
    letter-spacing: 0.3px;
}

/* ---------- Balance status pill ---------- */
.balanced-badge, .unbalanced-badge {
    font-family: 'IBM Plex Mono', monospace;
    font-size: 12px;
    font-weight: 600;
    letter-spacing: 0.2px;
    border-radius: 20px;
}
.balanced-badge { background: #e7f6ee; color: #17603f; border: 1px solid #b7e3cb; }
.unbalanced-badge { background: #fbeaea; color: #8f2f2f; border: 1px solid #eeb8b8; }

/* ---------- Filter toolbar ---------- */
.tb-filter-bar {
    background: var(--card);
    border: 1px solid var(--hairline);
    border-radius: 8px;
    padding: 12px 18px;
}
.tb-filter-bar .form-control {
    border: 1px solid var(--hairline);
    font-family: 'IBM Plex Mono', monospace;
    font-size: 13px;
    border-radius: 6px;
}
.tb-filter-bar .form-control:focus {
    border-color: var(--ink-soft);
    box-shadow: 0 0 0 3px rgba(27,32,54,0.08);
}
.tb-filter-bar .btn-primary {
    background: var(--ink);
    border-color: var(--ink);
    border-radius: 6px;
    font-weight: 500;
    letter-spacing: 0.2px;
}
.tb-filter-bar .btn-primary:hover { background: #2d3357; border-color: #2d3357; }
.tb-filter-bar .btn-outline-secondary {
    border-radius: 6px;
    border-color: var(--hairline);
    color: var(--ink-soft);
    font-weight: 500;
}
.tb-filter-bar .btn-outline-secondary:hover {
    background: var(--ink);
    border-color: var(--ink);
    color: #fff;
}

/* ---------- Table card ---------- */
.tb-card { border: 1px solid var(--hairline); border-radius: 10px; overflow: hidden; }
.tb-header {
    background: var(--ink);
    color: #fff;
    padding: 13px 20px;
    font-family: 'Source Serif 4', Georgia, serif;
    font-weight: 600;
    font-size: 15px;
}
.tb-header .tb-header-period {
    font-family: 'IBM Plex Mono', monospace;
    font-weight: 400;
    font-size: 12px;
    opacity: 0.7;
    letter-spacing: 0.2px;
}

.tb-table { width: 100%; border-collapse: collapse; }
.tb-table thead th {
    background: var(--paper);
    padding: 11px 16px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: var(--muted);
    border-bottom: 1px solid var(--hairline);
}
.tb-table td {
    padding: 10px 16px;
    font-size: 13.5px;
    border-bottom: 1px solid var(--hairline);
    color: var(--ink-soft);
}
.tb-table td.font-weight-600 {
    font-family: 'IBM Plex Mono', monospace;
    color: var(--muted);
    font-weight: 500;
}
.tb-table td.amount {
    text-align: right;
    font-family: 'IBM Plex Mono', monospace;
    font-variant-numeric: tabular-nums;
    color: var(--ink);
}
.tb-table tbody tr:hover td { background: #fafbff; }
.tb-table tbody tr:last-child td { border-bottom: none; }

/* ---------- Type badges (ink text on soft tint, not saturated fills) ---------- */
.type-badge { border-radius: 20px; padding: 3px 10px; font-size: 10px; font-weight: 600; letter-spacing: 0.3px; text-transform: uppercase; }
.tb-type-asset .type-badge { background: #e7f6ee; color: #17603f; }
.tb-type-liability .type-badge { background: #fbeaea; color: #8f2f2f; }
.tb-type-equity .type-badge { background: #f0ecfb; color: #4b2e9c; }
.tb-type-income .type-badge { background: #e8f1fc; color: #1a4f9c; }
.tb-type-expense .type-badge { background: #fdf3e2; color: #8a5a12; }

/* ---------- Footer / totals: classic accounting double rule ---------- */
.tb-footer td {
    font-weight: 700;
    background: var(--ink);
    color: #fff !important;
    border-top: 3px double var(--rule-gold);
    border-bottom: none;
    padding: 13px 16px;
}
.tb-footer td.amount { color: #6fe3b4 !important; font-size: 14.5px; }

.tb-empty { text-align: center; color: var(--muted); padding: 32px 0 !important; font-style: italic; font-size: 13px; }
</style>
@endpush

@section('content')
<div class="content-wrapper">
<div class="tb-report">

    <div class="tb-masthead">
        <div>
            <h1>@lang('modules.report.trialBalance')</h1>
            <span class="tb-period">{{ $fromDate }} &nbsp;&rarr;&nbsp; {{ $toDate }}</span>
        </div>
        <div>
            @if(abs($totalDebit - $totalCredit) < 0.01)
                <span class="badge balanced-badge py-2 px-3">✓ @lang('modules.report.balanced')</span>
            @else
                <span class="badge unbalanced-badge py-2 px-3">⚠ @lang('modules.report.unbalanced'): {{ number_format(abs($totalDebit - $totalCredit), 2) }}</span>
            @endif
        </div>
    </div>

    {{-- Date filter --}}
    <form method="GET" action="{{ route('travel-reports.trial-balance') }}" class="d-flex align-items-center gap-3 mb-4 tb-filter-bar">
        <input type="date" name="from_date" value="{{ $fromDate }}" class="form-control form-control-sm" style="width:150px">
        <span class="text-muted">→</span>
        <input type="date" name="to_date" value="{{ $toDate }}" class="form-control form-control-sm" style="width:150px">
        <button class="btn btn-primary btn-sm"><i class="fa fa-filter"></i> @lang('app.filter')</button>
        <a href="{{ route('travel-reports.export', 'trial-balance') }}?from_date={{ $fromDate }}&to_date={{ $toDate }}"
           class="btn btn-outline-secondary btn-sm ml-auto">
            <i class="fa fa-file-excel"></i> @lang('app.export')
        </a>
    </form>

    <div class="bg-white tb-card">
        <div class="tb-header d-flex justify-content-between align-items-center">
            <span>@lang('modules.report.trialBalance')</span>
            <span class="tb-header-period">{{ $fromDate }} — {{ $toDate }}</span>
        </div>
        <table class="tb-table">
            <thead>
                <tr>
                    <th>@lang('modules.accounting.code')</th>
                    <th>@lang('modules.accounting.accountName')</th>
                    <th>@lang('modules.accounting.type')</th>
                    <th class="text-right">@lang('modules.accounting.debit')</th>
                    <th class="text-right">@lang('modules.accounting.credit')</th>
                </tr>
            </thead>
            <tbody>
                @forelse($balances as $row)
                <tr class="tb-type-{{ $row->type }}">
                    <td class="font-weight-600">{{ $row->code }}</td>
                    <td>{{ $row->name }}</td>
                    <td><span class="type-badge">{{ ucfirst($row->type) }}</span></td>
                    <td class="amount">{{ $row->total_debit > 0 ? number_format($row->total_debit, 2) : '—' }}</td>
                    <td class="amount">{{ $row->total_credit > 0 ? number_format($row->total_credit, 2) : '—' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="tb-empty">
                        @lang('modules.report.noJournalPostings')
                    </td>
                </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr class="tb-footer">
                    <td colspan="3">@lang('modules.report.totals')</td>
                    <td class="amount">{{ number_format($totalDebit, 2) }}</td>
                    <td class="amount">{{ number_format($totalCredit, 2) }}</td>
                </tr>
            </tfoot>
        </table>
    </div>

</div>
</div>
@endsection