<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>@lang('app.voucher') - {{ $voucher->voucher_number }}</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 11px; color: #333; }
        .header { text-align: center; border-bottom: 2px solid #1a1f3c; padding-bottom: 15px; margin-bottom: 20px; }
        .header h1 { font-size: 20px; color: #1a1f3c; margin: 0 0 5px; }
        .header .subtitle { font-size: 13px; color: #666; }
        table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        th, td { padding: 6px 10px; text-align: left; border-bottom: 1px solid #ddd; font-size: 10px; }
        th { background: #f5f5f5; font-weight: 600; }
        .text-right { text-align: right; }
        .footer { margin-top: 30px; padding-top: 15px; border-top: 1px solid #ddd; font-size: 9px; text-align: center; color: #999; }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 10px; font-size: 9px; }
        .voucher-info { margin-bottom: 20px; }
        .voucher-info td { border: none; padding: 3px 10px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>@lang('app.voucher')</h1>
        <div class="subtitle">{{ $voucher->voucher_number }}</div>
    </div>

    <table class="voucher-info">
        <tr><td><strong>@lang('app.type'):</strong> {{ ucfirst($voucher->type) }}</td>
            <td><strong>@lang('app.date'):</strong> {{ $voucher->date ? \Carbon\Carbon::parse($voucher->date)->format('d M Y') : '—' }}</td></tr>
        <tr><td><strong>@lang('app.bookingGroup'):</strong> {{ $voucher->bookingGroup?->group_name ?? '—' }}</td>
            <td><strong>@lang('app.status'):</strong> {{ ucfirst($voucher->status) }}</td></tr>
    </table>

    <h4>@lang('modules.booking.passengerManifest')</h4>
    <table>
        <thead><tr><th>#</th><th>@lang('app.name')</th><th>@lang('app.passport')</th><th>@lang('app.gender')</th></tr></thead>
        <tbody>
            @forelse($voucher->bookingGroup?->passengers ?? [] as $i => $pax)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $pax->full_name }}</td>
                <td>{{ $pax->masked_passport_no }}</td>
                <td>{{ $pax->gender }}</td>
            </tr>
            @empty
            <tr><td colspan="4" style="text-align:center;color:#999">@lang('modules.booking.noPassengersYet')</td></tr>
            @endforelse
        </tbody>
    </table>

    <h4>@lang('app.charges')</h4>
    <table>
        <thead><tr><th>@lang('app.description')</th><th class="text-right">@lang('app.amount')</th></tr></thead>
        <tbody>
            @forelse($voucher->charges as $charge)
            <tr><td>{{ $charge->description ?? '—' }}</td><td class="text-right">{{ number_format($charge->amount ?? 0, 2) }}</td></tr>
            @empty
            <tr><td colspan="2" style="text-align:center;color:#999">@lang('messages.noRecordFound')</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>{{ $company->company_name ?? config('app.name') }} — @lang('app.generatedOn') {{ now()->format('d M Y H:i') }}</p>
        @if($voucher->qr_payload)
        <p><img src="data:image/png;base64,{{ base64_encode(\Endroid\QrCode\Builder\Builder::create()->writer(new \Endroid\QrCode\Writer\PngWriter())->data($voucher->qr_payload)->size(80)->build()->getString()) }}" width="80" height="80"></p>
        @endif
    </div>
</body>
</html>
