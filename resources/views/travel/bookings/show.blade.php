@extends('layouts.app')

@push('css')
<style>
/* ── Booking Detail ──────────────────────────────────────────────────────────── */
.booking-hero {
    background: linear-gradient(135deg, #1a1f3c 0%, #2d3561 100%);
    color: #fff;
    border-radius: 14px;
    padding: 28px 32px;
    margin-bottom: 24px;
    position: relative;
    overflow: hidden;
}
.booking-hero::before {
    content: '';
    position: absolute;
    right: -40px; top: -40px;
    width: 220px; height: 220px;
    background: rgba(255,255,255,.04);
    border-radius: 50%;
}
.booking-hero .group-code {
    font-size: 12px; opacity: .65; letter-spacing: 1px; text-transform: uppercase;
}
.booking-hero h3 { font-size: 22px; font-weight: 700; margin: 4px 0 12px; }
.hero-stat { display: inline-block; margin-right: 32px; }
.hero-stat .value { font-size: 24px; font-weight: 700; }
.hero-stat .label { font-size: 11px; opacity: .7; }
.status-pill {
    display: inline-block;
    padding: 3px 14px; border-radius: 20px;
    font-size: 11px; font-weight: 600; letter-spacing: .4px;
}
.status-confirmed { background: #d1fae5; color: #065f46; }
.status-pending   { background: #fef3c7; color: #92400e; }
.status-cancelled { background: #fee2e2; color: #991b1b; }
.status-completed { background: #dbeafe; color: #1d4ed8; }

/* Passengers table */
.pax-table { width: 100%; }
.pax-table th { font-size: 11px; text-transform: uppercase; color: #888; padding: 8px 12px; background: #f8f9fd; }
.pax-table td { padding: 9px 12px; font-size: 13px; border-bottom: 1px solid #f3f4f8; vertical-align: middle; }
.pax-table tr:hover { background: #fafbff; }
.passport-mono { font-family: 'Courier New', monospace; font-size: 12px; color: #555; }
.visa-chip { display: inline-block; padding: 2px 10px; border-radius: 20px; font-size: 10px; font-weight: 600; }
.visa-draft           { background: #e5e7eb; color: #6b7280; }
.visa-sent_to_embassy { background: #dbeafe; color: #1d4ed8; }
.visa-mofa_received   { background: #ede9fe; color: #5b21b6; }
.visa-issued          { background: #d1fae5; color: #065f46; }
.visa-rejected        { background: #fee2e2; color: #991b1b; }

.tab-pills .nav-link { border-radius: 8px; padding: 8px 18px; font-size: 13px; color: #555; }
.tab-pills .nav-link.active { background: #1a1f3c; color: #fff; }
</style>
@endpush

@section('content')
<div class="content-wrapper">

    {{-- Hero Header --}}
    <div class="booking-hero">
        <div class="group-code">{{ $booking->group_no ?? 'BOOKING #'.$booking->id }}</div>
        <h3>{{ $booking->group_name }}</h3>
        <div class="d-flex align-items-center flex-wrap gap-4">
            <div class="hero-stat">
                <div class="value">{{ $booking->passengers->count() }}</div>
                <div class="label">@lang('modules.booking.passengers')</div>
            </div>
            <div class="hero-stat">
                <div class="value">{{ $booking->departure_date ? \Carbon\Carbon::parse($booking->departure_date)->format('d M Y') : '—' }}</div>
                <div class="label">@lang('app.departureDate')</div>
            </div>
            <div class="hero-stat">
                <div class="value">{{ $booking->return_date ? \Carbon\Carbon::parse($booking->return_date)->format('d M Y') : '—' }}</div>
                <div class="label">@lang('app.returnDate')</div>
            </div>
            <div class="hero-stat">
                <div class="value">{{ $booking->package?->name ?? '—' }}</div>
                <div class="label">@lang('app.package')</div>
            </div>
            <div class="ml-auto">
                <span class="status-pill status-{{ $booking->status }}">{{ ucfirst($booking->status) }}</span>
            </div>
        </div>
    </div>

    {{-- Action Bar --}}
    <div class="d-flex align-items-center gap-2 mb-3">
        <a href="{{ route('bookings.import.page') }}?booking_group_id={{ $booking->id }}"
           class="btn btn-sm btn-primary">
            <i class="fa fa-upload"></i> @lang('modules.booking.importPassengers')
        </a>
        <button class="btn btn-sm btn-outline-primary openRightModal"
                data-href="{{ route('bookings.passengers.add', $booking->id) }}">
            <i class="fa fa-plus"></i> @lang('modules.booking.addPassenger')
        </button>
        <a href="{{ route('visa-pipeline.index') }}?booking_group_id={{ $booking->id }}"
           class="btn btn-sm btn-outline-secondary">
            <i class="fa fa-passport"></i> @lang('app.visaPipeline')
        </a>
        <a href="{{ route('room-allocation.index') }}?booking_group_id={{ $booking->id }}"
           class="btn btn-sm btn-outline-secondary">
            <i class="fa fa-bed"></i> @lang('app.roomAllocation')
        </a>
        <a href="{{ route('vouchers.create') }}?booking_group_id={{ $booking->id }}"
           class="btn btn-sm btn-outline-success">
            <i class="fa fa-file-alt"></i> @lang('app.createVoucher')
        </a>
        <a href="{{ route('bookings.edit', $booking->id) }}"
           class="btn btn-sm btn-outline-warning ml-auto">
            <i class="fa fa-edit"></i> @lang('app.edit')
        </a>
    </div>

    {{-- Tabs --}}
    <ul class="nav nav-pills tab-pills mb-3" id="bookingTabs" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" data-toggle="tab" href="#tab-passengers">
                <i class="fa fa-users mr-1"></i> @lang('modules.booking.passengers') ({{ $booking->passengers->count() }})
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-toggle="tab" href="#tab-financials">
                <i class="fa fa-money-bill mr-1"></i> @lang('app.financials')
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-toggle="tab" href="#tab-vouchers">
                <i class="fa fa-file-alt mr-1"></i> @lang('app.vouchers') ({{ $booking->vouchers?->count() ?? 0 }})
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-toggle="tab" href="#tab-rooms">
                <i class="fa fa-bed mr-1"></i> @lang('app.rooms')
            </a>
        </li>
    </ul>

    <div class="tab-content">

        {{-- Passengers Tab --}}
        <div class="tab-pane fade show active" id="tab-passengers">
            <div class="bg-white rounded shadow-sm overflow-hidden">
                <div class="d-flex justify-content-between align-items-center px-4 py-3 border-bottom">
                    <h6 class="mb-0">@lang('modules.booking.passengerManifest')</h6>
                    <div>
                        <input type="text" id="pax-search" class="form-control form-control-sm" placeholder="@lang('app.search')..." style="width:200px">
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="pax-table" id="passengers-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>@lang('modules.booking.passportNo')</th>
                                <th>@lang('app.name')</th>
                                <th>@lang('app.gender')</th>
                                <th>@lang('modules.booking.dob')</th>
                                <th>@lang('app.visaStatus')</th>
                                <th>@lang('modules.booking.room')</th>
                                <th>@lang('app.action')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($booking->passengers as $i => $pax)
                            <tr data-name="{{ strtolower($pax->full_name) }}" data-passport="{{ strtolower($pax->passport_no) }}">
                                <td class="text-muted">{{ $i + 1 }}</td>
                                <td><span class="passport-mono">{{ $pax->passport_no }}</span></td>
                                <td>
                                    <div class="font-weight-600">{{ $pax->full_name }}</div>
                                    @if($pax->visa_mofa_ref)
                                        <small class="text-muted">MoFA: {{ $pax->visa_mofa_ref }}</small>
                                    @endif
                                </td>
                                <td>
                                    <span class="{{ $pax->gender === 'Male' ? 'text-primary' : 'text-danger' }}">
                                        {{ $pax->gender === 'Male' ? '♂' : '♀' }} {{ $pax->gender }}
                                    </span>
                                </td>
                                <td>{{ $pax->birth_date ? \Carbon\Carbon::parse($pax->birth_date)->format('d/m/Y') : '—' }}</td>
                                <td>
                                    <span class="visa-chip visa-{{ $pax->visa_pipeline_status ?? 'draft' }}">
                                        {{ Passenger::VISA_STATUSES[$pax->visa_pipeline_status ?? 'draft'] ?? ucfirst($pax->visa_pipeline_status ?? 'Draft') }}
                                    </span>
                                </td>
                                <td>
                                    @if($pax->roomAllocation?->room_number)
                                        <span class="badge bg-light text-dark">{{ $pax->roomAllocation->room_number }}</span>
                                    @else
                                        <span class="text-muted small">—</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <a href="javascript:void(0)"
                                           class="btn btn-icon btn-sm btn-outline-secondary openRightModal"
                                           data-href="{{ route('bookings.passengers.add', $booking->id) }}?passenger_id={{ $pax->id }}"
                                           title="@lang('app.edit')">
                                            <i class="fa fa-edit"></i>
                                        </a>
                                        <button class="btn btn-icon btn-sm btn-outline-danger delete-pax"
                                                data-booking-id="{{ $booking->id }}"
                                                data-passenger-id="{{ $pax->id }}"
                                                title="@lang('app.remove')">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="fa fa-users fa-3x d-block mb-3"></i>
                                    @lang('modules.booking.noPassengersYet')
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Financials Tab --}}
        <div class="tab-pane fade" id="tab-financials">
            <div class="row">
                <div class="col-md-4">
                    <div class="bg-white rounded shadow-sm p-4">
                        <h6 class="mb-3">@lang('modules.booking.paymentSummary')</h6>
                        <div class="d-flex justify-content-between py-2 border-bottom">
                            <span class="text-muted">@lang('modules.booking.totalReceived')</span>
                            <strong class="text-success">
                                {{ number_format($booking->travelPayments->where('payment_direction','receive')->sum('amount'), 2) }}
                            </strong>
                        </div>
                        <div class="d-flex justify-content-between py-2 border-bottom">
                            <span class="text-muted">@lang('modules.booking.totalPaid')</span>
                            <strong class="text-danger">
                                {{ number_format($booking->travelPayments->where('payment_direction','make')->sum('amount'), 2) }}
                            </strong>
                        </div>
                        <div class="d-flex justify-content-between py-2">
                            <span class="font-weight-bold">@lang('modules.booking.netBalance')</span>
                            @php $net = $booking->travelPayments->where('payment_direction','receive')->sum('amount') - $booking->travelPayments->where('payment_direction','make')->sum('amount'); @endphp
                            <strong class="{{ $net >= 0 ? 'text-success' : 'text-danger' }}">
                                {{ number_format(abs($net), 2) }}
                            </strong>
                        </div>
                        <div class="mt-3 d-flex gap-2">
                            <a href="{{ route('travel-payments.receive.create') }}?booking_group_id={{ $booking->id }}"
                               class="btn btn-sm btn-success flex-fill text-center">
                                <i class="fa fa-arrow-down"></i> @lang('app.receivePayment')
                            </a>
                            <a href="{{ route('travel-payments.make.create') }}?booking_group_id={{ $booking->id }}"
                               class="btn btn-sm btn-danger flex-fill text-center">
                                <i class="fa fa-arrow-up"></i> @lang('app.makePayment')
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="bg-white rounded shadow-sm overflow-hidden">
                        <div class="px-4 py-3 border-bottom"><h6 class="mb-0">@lang('modules.booking.paymentHistory')</h6></div>
                        <table class="table table-sm mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>@lang('app.date')</th>
                                    <th>@lang('app.type')</th>
                                    <th>@lang('app.reference')</th>
                                    <th class="text-right">@lang('app.amount')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($booking->travelPayments as $pmt)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($pmt->payment_date)->format('d M Y') }}</td>
                                    <td>
                                        <span class="badge {{ $pmt->payment_direction === 'receive' ? 'badge-success' : 'badge-danger' }}">
                                            {{ ucfirst($pmt->payment_direction) }}
                                        </span>
                                    </td>
                                    <td>{{ $pmt->reference_no ?? '—' }}</td>
                                    <td class="text-right font-weight-bold {{ $pmt->payment_direction === 'receive' ? 'text-success' : 'text-danger' }}">
                                        {{ number_format($pmt->amount, 2) }}
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="4" class="text-center text-muted py-3">@lang('modules.booking.noPayments')</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Vouchers Tab --}}
        <div class="tab-pane fade" id="tab-vouchers">
            <div class="bg-white rounded shadow-sm p-4">
                @forelse($booking->vouchers ?? [] as $voucher)
                <div class="d-flex align-items-center p-3 border rounded mb-2">
                    <div class="flex-grow-1">
                        <div class="font-weight-600">{{ $voucher->voucher_no }}</div>
                        <small class="text-muted">{{ $voucher->voucher_type }} — {{ ucfirst($voucher->status) }}</small>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('vouchers.pdf', $voucher->id) }}" class="btn btn-sm btn-outline-secondary">
                            <i class="fa fa-pdf"></i> PDF
                        </a>
                        <a href="{{ route('vouchers.show', $voucher->id) }}" class="btn btn-sm btn-outline-primary">
                            @lang('app.view')
                        </a>
                    </div>
                </div>
                @empty
                <div class="text-center py-5 text-muted">
                    <i class="fa fa-file-alt fa-3x d-block mb-3"></i>
                    @lang('modules.booking.noVouchers')
                </div>
                @endforelse
            </div>
        </div>

        {{-- Rooms Tab --}}
        <div class="tab-pane fade" id="tab-rooms">
            <div class="text-center py-4">
                <a href="{{ route('room-allocation.index') }}?booking_group_id={{ $booking->id }}"
                   class="btn btn-primary">
                    <i class="fa fa-external-link-alt"></i>
                    @lang('modules.room.openAllocationGrid')
                </a>
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
'use strict';

// Live passenger search
$('#pax-search').on('input', function() {
    const q = $(this).val().toLowerCase();
    $('#passengers-table tbody tr').each(function() {
        const name     = $(this).data('name') || '';
        const passport = $(this).data('passport') || '';
        $(this).toggle(name.includes(q) || passport.includes(q));
    });
});

// Remove passenger
$('body').on('click', '.delete-pax', function() {
    const bookingId   = $(this).data('booking-id');
    const passengerId = $(this).data('passenger-id');
    const row         = $(this).closest('tr');

    Swal.fire({
        title: "@lang('messages.sweetAlertTitle')",
        text:  "@lang('modules.booking.confirmRemovePassenger')",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: "@lang('app.remove')",
        cancelButtonText: "@lang('app.cancel')",
        customClass: { confirmButton: 'btn btn-danger mr-3', cancelButton: 'btn btn-secondary' },
        buttonsStyling: false,
    }).then(result => {
        if (result.isConfirmed) {
            $.easyAjax({
                url: `/bookings/${bookingId}/passengers/${passengerId}`,
                type: 'DELETE',
                data: { _token: '{{ csrf_token() }}' },
                success: function(response) {
                    if (response.status === 'success') {
                        row.fadeOut(300, () => row.remove());
                        window.toastr.success(response.message);
                    }
                }
            });
        }
    });
});
</script>
@endpush
