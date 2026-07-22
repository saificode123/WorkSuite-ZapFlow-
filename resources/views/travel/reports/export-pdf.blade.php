<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ ucfirst(str_replace('-', ' ', $report)) }}</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 10px; color: #333; }
        .header { text-align: center; border-bottom: 2px solid #1a1f3c; padding-bottom: 10px; margin-bottom: 15px; }
        .header h2 { font-size: 16px; color: #1a1f3c; margin: 0; }
        .header p { font-size: 10px; color: #666; margin: 4px 0 0; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th, td { padding: 5px 8px; text-align: left; border-bottom: 1px solid #ddd; font-size: 9px; }
        th { background: #f5f5f5; font-weight: 600; }
        .text-right { text-align: right; }
        .total-row { font-weight: 700; background: #f9fafb; }
        .footer { margin-top: 20px; padding-top: 10px; border-top: 1px solid #ddd; font-size: 8px; text-align: center; color: #999; }
    </style>
</head>
<body>
    <div class="header">
        <h2>{{ ucfirst(str_replace('-', ' ', $report)) }}</h2>
        <p>{{ $company->company_name ?? config('app.name') }} | {{ $fromDate }} — {{ $toDate }}</p>
    </div>

    @if($report === 'trial-balance' && !empty($data))
    <table>
        <thead><tr><th>@lang('app.code')</th><th>@lang('app.account')</th><th>@lang('app.type')</th><th class="text-right">@lang('app.debit')</th><th class="text-right">@lang('app.credit')</th></tr></thead>
        <tbody>
            @php $td = 0; $tc = 0; @endphp
            @foreach($data as $row)
            <tr>
                <td>{{ $row->code ?? '—' }}</td>
                <td>{{ $row->name }}</td>
                <td>{{ $row->type }}</td>
                <td class="text-right">{{ number_format($row->total_debit ?? 0, 2) }}</td>
                <td class="text-right">{{ number_format($row->total_credit ?? 0, 2) }}</td>
            </tr>
            @php $td += $row->total_debit ?? 0; $tc += $row->total_credit ?? 0; @endphp
            @endforeach
            <tr class="total-row">
                <td colspan="3">@lang('app.total')</td>
                <td class="text-right">{{ number_format($td, 2) }}</td>
                <td class="text-right">{{ number_format($tc, 2) }}</td>
            </tr>
        </tbody>
    </table>
    @endif

    <div class="footer">
        @lang('app.generatedOn') {{ now()->format('d M Y H:i') }}
    </div>
</body>
</html>
