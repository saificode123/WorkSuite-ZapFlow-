<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>@lang('app.voucher') - {{ $voucher->voucher_number }}</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 11px; color: #333; }

        .header { text-align: center; background: #1a1f3c; padding: 18px 15px; margin-bottom: 20px; border-radius: 4px; }
        .header h1 { font-size: 20px; color: #ffffff; margin: 0 0 6px; letter-spacing: 1px; }
        .header .subtitle { font-size: 13px; color: #c7cbe0; font-family: 'DejaVu Sans Mono', monospace; }

        .voucher-info { width: 100%; margin-bottom: 20px; background: #f8f9fc; border: 1px solid #e5e7ef; border-radius: 4px; border-collapse: collapse; }
        .voucher-info td { border: none; padding: 8px 14px; font-size: 10px; }
        .voucher-info strong { color: #1a1f3c; }

        h4 { font-size: 12px; color: #1a1f3c; margin: 18px 0 6px; padding-bottom: 4px; border-bottom: 2px solid #1a1f3c; }

        table.data-table { width: 100%; border-collapse: collapse; margin: 8px 0 15px; }
        table.data-table th, table.data-table td { padding: 6px 10px; text-align: left; border-bottom: 1px solid #ddd; font-size: 10px; }
        table.data-table th { background: #f5f5f5; font-weight: 600; color: #1a1f3c; }
        table.data-table tbody tr:nth-child(even) { background: #fafafc; }

        .text-right { text-align: right; }

        .badge { display: inline-block; padding: 2px 10px; border-radius: 10px; font-size: 9px; font-weight: 600; }
        .badge-draft { background: #e9ecef; color: #495057; }
        .badge-issued { background: #d4f4e2; color: #14663f; }
        .badge-cancelled { background: #fbe0e0; color: #96262a; }

        .footer { margin-top: 30px; padding-top: 15px; border-top: 1px solid #ddd; font-size: 9px; text-align: center; color: #999; }
        .qr-wrap { margin-top: 10px; }
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
            <td><strong>@lang('app.status'):</strong>
                @php
                    $statusBadgeClass = match($voucher->status) {
                        'issued' => 'badge-issued',
                        'cancelled' => 'badge-cancelled',
                        default => 'badge-draft',
                    };
                @endphp
                <span class="badge {{ $statusBadgeClass }}">{{ ucfirst($voucher->status) }}</span>
            </td></tr>
    </table>

    <h4>@lang('modules.booking.passengerManifest')</h4>
    <table class="data-table">
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
    <table class="data-table">
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
        <div class="qr-wrap">
            <img src="data:image/png;base64,{{ base64_encode(\Endroid\QrCode\Builder\Builder::create()->writer(new \Endroid\QrCode\Writer\PngWriter())->data($voucher->qr_payload)->size(80)->build()->getString()) }}" width="80" height="80">
        </div>
        @endif
    </div>
</body>
</html>