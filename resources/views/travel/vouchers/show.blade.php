@extends('layouts.app')

@push('css')
<style>
.voucher-header { background: linear-gradient(135deg, #1a1f3c 0%, #2d3561 100%); color: #fff; border-radius: 14px; padding: 28px 32px; margin-bottom: 24px; }
.voucher-header .v-type { font-size: 12px; opacity: .65; letter-spacing: 1px; text-transform: uppercase; }
.voucher-header h3 { font-size: 22px; font-weight: 700; margin: 4px 0 12px; }
.status-badge { display: inline-block; padding: 3px 14px; border-radius: 20px; font-size: 11px; font-weight: 600; }
.status-draft { background: #fef3c7; color: #92400e; }
.status-locked { background: #dbeafe; color: #1d4ed8; }
.status-issued { background: #d1fae5; color: #065f46; }
</style>
@endpush

@section('content')
<div class="content-wrapper">
    <div class="voucher-header">
        <div class="v-type">{{ __('modules.vouchers.' . $voucher->type) ?? ucfirst($voucher->type) }} @lang('app.voucher')</div>
        <h3>{{ $voucher->voucher_number }}</h3>
        <div class="d-flex align-items-center flex-wrap gap-4">
            <div><strong>@lang('app.bookingGroup'):</strong> {{ $voucher->bookingGroup?->group_name ?? '—' }}</div>
            <div><strong>@lang('app.date'):</strong> {{ $voucher->date ? \Carbon\Carbon::parse($voucher->date)->format('d M Y') : '—' }}</div>
            <div><strong>@lang('app.version'):</strong> {{ $voucher->version }}</div>
            <div class="ml-auto">
                <span class="status-badge status-{{ $voucher->status }}">{{ __( 'modules.vouchers.status' . ucfirst($voucher->status)) ?? ucfirst($voucher->status) }}</span>
            </div>
        </div>
    </div>

    <div class="d-flex gap-2 mb-3 flex-wrap">
        @if(!$voucher->isLocked())
            <form method="POST" action="{{ route('vouchers.lock', $voucher->id) }}" style="display:inline">
                @csrf
                <button class="btn btn-sm btn-warning" onclick="return confirm('@lang('messages.confirmLockVoucher')')">
                    <i class="fa fa-lock"></i> @lang('app.lockVoucher')
                </button>
            </form>
            <form method="POST" action="{{ route('vouchers.issue', $voucher->id) }}" style="display:inline">
                @csrf
                <button class="btn btn-sm btn-success" onclick="return confirm('@lang('messages.confirmIssueVoucher')')">
                    <i class="fa fa-check-circle"></i> @lang('app.issueVoucher')
                </button>
            </form>
            <a href="{{ route('vouchers.edit', $voucher->id) }}" class="btn btn-sm btn-outline-primary openRightModal">
                <i class="fa fa-edit"></i> @lang('app.edit')
            </a>
        @endif
        <a href="{{ route('vouchers.pdf', $voucher->id) }}" class="btn btn-sm btn-outline-secondary" target="_blank">
            <i class="fa fa-file-pdf"></i> @lang('app.downloadPdf')
        </a>
        <a href="{{ route('vouchers.qr', $voucher->id) }}" class="btn btn-sm btn-outline-secondary" target="_blank">
            <i class="fa fa-qrcode"></i> @lang('app.viewQrCode')
        </a>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header"><h6 class="mb-0">@lang('modules.booking.passengerManifest')</h6></div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                        <thead><tr><th>#</th><th>@lang('app.name')</th><th>@lang('app.passport')</th><th>@lang('app.gender')</th></tr></thead>
                        <tbody>
                            @forelse($voucher->bookingGroup?->passengers ?? [] as $i => $pax)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td>{{ $pax->full_name }}</td>
                                <td class="passport-mono">{{ $pax->masked_passport_no }}</td>
                                <td>{{ $pax->gender }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="text-center text-muted py-3">@lang('modules.booking.noPassengersYet')</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header"><h6 class="mb-0">@lang('app.charges')</h6></div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                        <thead><tr><th>@lang('app.description')</th><th class="text-right">@lang('app.amount')</th></tr></thead>
                        <tbody>
                            @forelse($voucher->charges as $charge)
                            <tr>
                                <td>{{ $charge->description ?? '—' }}</td>
                                <td class="text-right">{{ number_format($charge->amount ?? 0, 2) }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="2" class="text-center text-muted py-3">@lang('messages.noRecordFound')</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
