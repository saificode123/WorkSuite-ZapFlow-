<!DOCTYPE html>
<html>
<head>
    <title>@lang('modules.accounts.journalVoucher') - {{ $voucher->voucher_number }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h2 { margin: 0; }
        .meta { margin-bottom: 15px; }
        .meta table { width: 100%; }
        table.details { width: 100%; border-collapse: collapse; }
        table.details th, table.details td { border: 1px solid #000; padding: 5px; }
        table.details th { background: #f0f0f0; }
        .text-right { text-align: right; }
        .footer { margin-top: 30px; }
        .footer-table { width: 100%; }
        .footer-table td { padding: 10px; }
        .signature-line { border-top: 1px solid #000; width: 200px; display: inline-block; }
    </style>
</head>
<body>
    <div class="header">
        <h2>{{ company()->company_name }}</h2>
        <h3>@lang('modules.accounts.journalVoucher')</h3>
    </div>
    <div class="meta">
        <table>
            <tr>
                <td><strong>@lang('modules.accounts.voucherNumber'):</strong> {{ $voucher->voucher_number }}</td>
                <td><strong>@lang('app.date'):</strong> {{ $voucher->date->format(company()->date_format) }}</td>
            </tr>
            <tr>
                <td><strong>@lang('modules.accounts.financialYear'):</strong> {{ $voucher->financialYear->name ?? '--' }}</td>
                <td></td>
            </tr>
        </table>
    </div>
    <table class="details">
        <thead>
            <tr>
                <th>#</th>
                <th>@lang('modules.accounts.account')</th>
                <th>@lang('modules.accounts.description')</th>
                <th class="text-right">@lang('modules.accounts.debit')</th>
                <th class="text-right">@lang('modules.accounts.credit')</th>
            </tr>
        </thead>
        <tbody>
            @foreach($voucher->lines as $line)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $line->account->name ?? '--' }}</td>
                    <td>{{ $line->description ?? '--' }}</td>
                    <td class="text-right">{{ number_format($line->debit, 2) }}</td>
                    <td class="text-right">{{ number_format($line->credit, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th colspan="3" class="text-right">@lang('app.total')</th>
                <th class="text-right">{{ number_format($voucher->lines->sum('debit'), 2) }}</th>
                <th class="text-right">{{ number_format($voucher->lines->sum('credit'), 2) }}</th>
            </tr>
        </tfoot>
    </table>
    @if($voucher->narration)
        <p><strong>@lang('modules.accounts.narration'):</strong> {{ $voucher->narration }}</p>
    @endif
    <div class="footer">
        <table class="footer-table">
            <tr>
                <td class="text-center"><span class="signature-line"></span><br>@lang('app.preparedBy')</td>
                <td class="text-center"><span class="signature-line"></span><br>@lang('app.authorizedBy')</td>
            </tr>
        </table>
    </div>
    <script>window.print();</script>
</body>
</html>
