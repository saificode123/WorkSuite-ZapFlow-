<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ ucfirst(str_replace('-', ' ', $report)) }}</title>
    <style>
        @page {
            margin: 25px 30px;
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10px;
            color: #333;
        }

        /* Header */
        .header {
            text-align: center;
            border-bottom: 2px solid #1a1f3c;
            padding-bottom: 12px;
            margin-bottom: 18px;
        }
        .header .company-name {
            font-size: 13px;
            font-weight: 700;
            color: #1a1f3c;
            margin: 0 0 4px;
            letter-spacing: 0.3px;
        }
        .header h2 {
            font-size: 16px;
            color: #1a1f3c;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .header p {
            font-size: 9px;
            color: #777;
            margin: 6px 0 0;
        }
        .header .date-range {
            display: inline-block;
            background: #f5f6fa;
            border-radius: 3px;
            padding: 3px 10px;
            margin-top: 6px;
            font-size: 9px;
            color: #555;
        }

        /* Table */
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }
        thead tr {
            border-top: 1px solid #1a1f3c;
            border-bottom: 1px solid #1a1f3c;
        }
        th, td {
            padding: 6px 8px;
            text-align: left;
            border-bottom: 1px solid #eee;
            font-size: 9px;
        }
        th {
            background: #f5f6fa;
            font-weight: 700;
            color: #1a1f3c;
            text-transform: uppercase;
            font-size: 8px;
            letter-spacing: 0.3px;
        }
        tbody tr:nth-child(even) {
            background: #fafbfc;
        }
        .text-right { text-align: right; }
        .text-muted { color: #999; }

        .total-row td {
            font-weight: 700;
            background: #f0f2f7;
            border-top: 2px solid #1a1f3c;
            border-bottom: none;
            color: #1a1f3c;
        }

        /* Footer */
        .footer {
            margin-top: 25px;
            padding-top: 10px;
            border-top: 1px solid #ddd;
            font-size: 8px;
            text-align: center;
            color: #999;
        }
    </style>
</head>
<body>
    <div class="header">
        <p class="company-name">{{ $company->company_name ?? config('app.name') }}</p>
        <h2>{{ ucfirst(str_replace('-', ' ', $report)) }}</h2>
        <div class="date-range">{{ $fromDate }} &nbsp;&mdash;&nbsp; {{ $toDate }}</div>
    </div>

    @if($report === 'trial-balance' && !empty($data))
    <table>
        <thead>
            <tr>
                <th>@lang('app.code')</th>
                <th>@lang('app.account')</th>
                <th>@lang('app.type')</th>
                <th class="text-right">@lang('app.debit')</th>
                <th class="text-right">@lang('app.credit')</th>
            </tr>
        </thead>
        <tbody>
            @php $td = 0; $tc = 0; @endphp
            @foreach($data as $row)
            <tr>
                <td>{{ $row->code ?? '—' }}</td>
                <td>{{ $row->name }}</td>
                <td class="text-muted">{{ $row->type }}</td>
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