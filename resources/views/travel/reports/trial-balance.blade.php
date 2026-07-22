@extends('layouts.app')

@push('css')
<style>
.tb-header { background: #1a1f3c; color: #fff; padding: 12px 20px; border-radius: 8px 8px 0 0; }
.tb-table { width: 100%; border-collapse: collapse; }
.tb-table thead th { background: #f4f6fb; padding: 10px 16px; font-size: 12px; font-weight: 600; text-transform: uppercase; color: #555; }
.tb-table td { padding: 9px 16px; font-size: 13px; border-bottom: 1px solid #f0f2f8; }
.tb-table td.amount { text-align: right; font-family: 'Courier New', monospace; }
.tb-table tr:hover { background: #fafbff; }
.tb-footer td { font-weight: 700; background: #1a1f3c; color: #fff !important; }
.tb-footer td.amount { color: #5fffbf !important; }
.tb-type-asset .type-badge { background: #d1fae5; color: #065f46; }
.tb-type-liability .type-badge { background: #fee2e2; color: #991b1b; }
.tb-type-equity .type-badge { background: #ede9fe; color: #5b21b6; }
.tb-type-income .type-badge { background: #dbeafe; color: #1d4ed8; }
.tb-type-expense .type-badge { background: #fef3c7; color: #92400e; }
.type-badge { border-radius: 20px; padding: 2px 9px; font-size: 10px; font-weight: 600; }
.balanced-badge { background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
.unbalanced-badge { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }
</style>
@endpush

@section('content')
<div class="content-wrapper">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0">@lang('modules.report.trialBalance')</h4>
            <small class="text-muted">{{ $fromDate }} → {{ $toDate }}</small>
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
    <form method="GET" action="{{ route('travel-reports.trial-balance') }}" class="d-flex align-items-center gap-3 mb-3 bg-white rounded p-3 shadow-sm">
        <input type="date" name="from_date" value="{{ $fromDate }}" class="form-control form-control-sm" style="width:150px">
        <span class="text-muted">→</span>
        <input type="date" name="to_date" value="{{ $toDate }}" class="form-control form-control-sm" style="width:150px">
        <button class="btn btn-primary btn-sm"><i class="fa fa-filter"></i> @lang('app.filter')</button>
        <a href="{{ route('travel-reports.export', 'trial-balance') }}?from_date={{ $fromDate }}&to_date={{ $toDate }}"
           class="btn btn-outline-secondary btn-sm ml-auto">
            <i class="fa fa-file-excel"></i> @lang('app.export')
        </a>
    </form>

    <div class="bg-white rounded shadow-sm overflow-hidden">
        <div class="tb-header d-flex justify-content-between">
            <span>@lang('modules.report.trialBalance')</span>
            <span class="small opacity-75">{{ $fromDate }} — {{ $toDate }}</span>
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
                    <td colspan="5" class="text-center text-muted py-5">
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
@endsection
