@extends('layouts.app')

@push('css')
<style>
.pl-section { margin-bottom: 2rem; }
.pl-section-header {
    background: linear-gradient(135deg, #1a1f3c 0%, #2d3561 100%);
    color: #fff;
    padding: 10px 18px;
    border-radius: 8px 8px 0 0;
    font-weight: 600;
    font-size: 14px;
    display: flex;
    justify-content: space-between;
}
.pl-table { width: 100%; border-collapse: collapse; }
.pl-table td { padding: 9px 18px; font-size: 13px; border-bottom: 1px solid #f0f2f8; }
.pl-table td:last-child { text-align: right; font-family: 'Courier New', monospace; font-weight: 500; }
.pl-table tr:hover { background: #fafbff; }
.pl-total-row td { font-weight: 700; background: #f4f6fb; border-top: 2px solid #dde1ef; }
.pl-net-row td { font-weight: 700; font-size: 15px; background: #1a1f3c; color: #fff !important; }
.pl-net-row td:last-child { color: #5fffbf !important; }
.summary-card { background: #fff; border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,.07); padding: 20px 28px; }
.summary-card .label { font-size: 12px; color: #888; text-transform: uppercase; letter-spacing: .5px; }
.summary-card .value { font-size: 26px; font-weight: 700; margin-top: 4px; }
.value.positive { color: #198754; }
.value.negative { color: #dc3545; }
.date-filter-bar { background: #fff; border-radius: 10px; padding: 14px 18px; box-shadow: 0 1px 6px rgba(0,0,0,.06); margin-bottom: 1.5rem; }
</style>
@endpush

@section('content')
<div class="content-wrapper">

    {{-- Date Filter --}}
    <form method="GET" action="{{ route('travel-reports.profit-loss') }}" id="pl-filter-form">
        <div class="date-filter-bar d-flex align-items-center flex-wrap gap-3">
            <div class="d-flex align-items-center gap-2">
                <label class="mb-0 text-muted small">@lang('app.from')</label>
                <input type="date" name="from_date" value="{{ $fromDate }}" class="form-control form-control-sm" style="width:150px">
            </div>
            <div class="d-flex align-items-center gap-2">
                <label class="mb-0 text-muted small">@lang('app.to')</label>
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
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="summary-card">
                <div class="label">@lang('modules.report.totalRevenue')</div>
                <div class="value positive">{{ number_format($revenue->sum('net'), 2) }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="summary-card">
                <div class="label">@lang('modules.report.grossProfit')</div>
                <div class="value {{ $grossProfit >= 0 ? 'positive' : 'negative' }}">{{ number_format($grossProfit, 2) }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="summary-card">
                <div class="label">@lang('modules.report.totalExpenses')</div>
                <div class="value negative">{{ number_format($expenses->sum('net'), 2) }}</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="summary-card" style="border-left: 4px solid {{ $netProfit >= 0 ? '#198754' : '#dc3545' }}">
                <div class="label">@lang('modules.report.netProfit')</div>
                <div class="value {{ $netProfit >= 0 ? 'positive' : 'negative' }}">{{ number_format(abs($netProfit), 2) }} {{ $netProfit < 0 ? '(LOSS)' : '' }}</div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6">

            {{-- Revenue --}}
            <div class="pl-section bg-white rounded shadow-sm">
                <div class="pl-section-header">
                    <span>@lang('modules.report.revenue')</span>
                    <span>{{ number_format($revenue->sum('net'), 2) }}</span>
                </div>
                <table class="pl-table">
                    @foreach($revenue as $acc)
                    <tr>
                        <td>{{ $acc->code }} — {{ $acc->name }}</td>
                        <td>{{ number_format($acc->net, 2) }}</td>
                    </tr>
                    @endforeach
                    @if($revenue->isEmpty())
                    <tr><td colspan="2" class="text-center text-muted py-3">@lang('modules.report.noData')</td></tr>
                    @endif
                    <tr class="pl-total-row">
                        <td>@lang('modules.report.totalRevenue')</td>
                        <td>{{ number_format($revenue->sum('net'), 2) }}</td>
                    </tr>
                </table>
            </div>

            {{-- COGS --}}
            <div class="pl-section bg-white rounded shadow-sm mt-3">
                <div class="pl-section-header" style="background: linear-gradient(135deg,#6c2bd9,#8b5cf6)">
                    <span>@lang('modules.report.cogs')</span>
                    <span>{{ number_format($cogs->sum('net'), 2) }}</span>
                </div>
                <table class="pl-table">
                    @foreach($cogs as $acc)
                    <tr>
                        <td>{{ $acc->code }} — {{ $acc->name }}</td>
                        <td>{{ number_format($acc->net, 2) }}</td>
                    </tr>
                    @endforeach
                    @if($cogs->isEmpty())
                    <tr><td colspan="2" class="text-center text-muted py-3">@lang('modules.report.noData')</td></tr>
                    @endif
                    <tr class="pl-total-row">
                        <td>@lang('modules.report.totalCogs')</td>
                        <td>{{ number_format($cogs->sum('net'), 2) }}</td>
                    </tr>
                </table>
            </div>

            {{-- Gross Profit row --}}
            <table class="pl-table mt-1">
                <tr class="pl-net-row">
                    <td>@lang('modules.report.grossProfit')</td>
                    <td>{{ number_format($grossProfit, 2) }}</td>
                </tr>
            </table>

        </div>
        <div class="col-lg-6">

            {{-- Expenses --}}
            <div class="pl-section bg-white rounded shadow-sm">
                <div class="pl-section-header" style="background: linear-gradient(135deg,#dc3545,#f97316)">
                    <span>@lang('modules.report.expenses')</span>
                    <span>{{ number_format($expenses->sum('net'), 2) }}</span>
                </div>
                <table class="pl-table">
                    @foreach($expenses as $acc)
                    <tr>
                        <td>{{ $acc->code }} — {{ $acc->name }}</td>
                        <td>{{ number_format(abs($acc->net), 2) }}</td>
                    </tr>
                    @endforeach
                    @if($expenses->isEmpty())
                    <tr><td colspan="2" class="text-center text-muted py-3">@lang('modules.report.noData')</td></tr>
                    @endif
                    <tr class="pl-total-row">
                        <td>@lang('modules.report.totalExpenses')</td>
                        <td>{{ number_format($expenses->sum(fn($e) => abs($e->net)), 2) }}</td>
                    </tr>
                </table>
            </div>

            @if($otherIncome->isNotEmpty())
            {{-- Other Income --}}
            <div class="pl-section bg-white rounded shadow-sm mt-3">
                <div class="pl-section-header" style="background: linear-gradient(135deg,#0891b2,#06b6d4)">
                    <span>@lang('modules.report.otherIncome')</span>
                    <span>{{ number_format($otherIncome->sum('net'), 2) }}</span>
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
            <table class="pl-table mt-3">
                <tr class="pl-net-row" style="background: {{ $netProfit >= 0 ? '#065f46' : '#7f1d1d' }}">
                    <td style="font-size:16px">
                        @lang('modules.report.netProfit')
                        @if($netProfit < 0) <span class="badge bg-danger ml-2">LOSS</span> @endif
                    </td>
                    <td style="font-size:18px">{{ number_format(abs($netProfit), 2) }}</td>
                </tr>
            </table>

        </div>
    </div>

</div>
@endsection
