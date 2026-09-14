@extends('layouts.app')

@push('css')
<style>
@import url('https://fonts.googleapis.com/css2?family=Source+Serif+4:opsz,wght@8..60,600;8..60,700&family=Inter:wght@400;500;600&family=IBM+Plex+Mono:wght@400;500;600&display=swap');
.rpt-box { --ink:#1b2036; --muted:#7d8299; --card:#ffffff; --line:#e3e5f0; font-family:'Inter',sans-serif; color:var(--ink); }
.rpt-mast { display:flex; justify-content:space-between; align-items:baseline; flex-wrap:wrap; gap:10px; padding:20px 24px; border-bottom:2px solid var(--ink); background:var(--card); border-radius:8px 8px 0 0; }
.rpt-mast h1 { font-family:'Source Serif 4',Georgia,serif; font-size:24px; font-weight:600; margin:0; }
.rpt-filter { background:var(--card); border:1px solid var(--line); border-radius:8px; padding:14px 18px; margin:15px 0; }
.rpt-table { width:100%; border-collapse:collapse; background:var(--card); border:1px solid var(--line); border-radius:8px; }
.rpt-table th { background:#fafbfe; border-bottom:2px solid var(--line); padding:11px 14px; font-size:12px; text-transform:uppercase; color:var(--muted); font-weight:600; }
.rpt-table td { padding:11px 14px; border-bottom:1px solid var(--line); font-size:13px; }
.rpt-table .hotel-header { background:#f0f4ff; font-weight:700; }
.font-mono { font-family:'IBM Plex Mono',monospace; }
.status-confirmed { background:#e7f8f1; color:#17a673; padding:2px 7px; border-radius:8px; font-size:11px; font-weight:600; }
.status-pending { background:#fef4e2; color:#e8960c; padding:2px 7px; border-radius:8px; font-size:11px; font-weight:600; }
</style>
@endpush

@section('content')
<div class="content-wrapper rpt-box">
    <div class="rpt-mast">
        <div>
            <h1><i class="fa fa-kaaba mr-2 text-success"></i>{{ $city }} Hotel Check-In / Check-Out Report</h1>
            <p class="text-muted mb-0">Room Allocation Register — Grouped by Hotel</p>
        </div>
        <div>
            <button onclick="window.print()" class="btn btn-sm btn-outline-dark"><i class="fa fa-print mr-1"></i>Print</button>
        </div>
    </div>

    <div class="rpt-filter">
        <form method="GET" action="{{ route('travel-reports.makkah-hotel') }}" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label text-muted font-weight-bold" style="font-size:12px;">Period From</label>
                <input type="date" name="from_date" class="form-control form-control-sm font-mono" value="{{ request('from_date', $fromDate) }}">
            </div>
            <div class="col-md-3">
                <label class="form-label text-muted font-weight-bold" style="font-size:12px;">Period To</label>
                <input type="date" name="to_date" class="form-control form-control-sm font-mono" value="{{ request('to_date', $toDate) }}">
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-sm btn-primary"><i class="fa fa-filter mr-1"></i>Filter</button>
                <a href="{{ route('travel-reports.makkah-hotel') }}" class="btn btn-sm btn-light border ml-1"><i class="fa fa-times mr-1"></i>Reset</a>
            </div>
        </form>
    </div>

    @php
        $grouped = collect($allocations)->groupBy('hotel_name');
    @endphp

    <div class="table-responsive">
        <table class="rpt-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Passenger</th>
                    <th>Passport No</th>
                    <th>Gender</th>
                    <th>Room No</th>
                    <th>Room Type</th>
                    <th>Capacity</th>
                    <th>Check-In</th>
                    <th>Check-Out</th>
                    <th>Nights</th>
                    <th>Group</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($grouped as $hotelName => $rows)
                <tr class="hotel-header">
                    <td colspan="12">
                        <i class="fa fa-hotel mr-1"></i> {{ $hotelName }}
                        @php
                            $firstRow = $rows->first();
                            echo ' — ' . ($firstRow->city ?? '') . ($firstRow->stars ? ' (' . $firstRow->stars . '★)' : '');
                        @endphp
                        <span class="text-muted ml-2 font-weight-normal">({{ $rows->count() }} pax)</span>
                    </td>
                </tr>
                @foreach($rows as $idx => $a)
                @php
                    $nights = ($a->check_in && $a->check_out)
                        ? \Carbon\Carbon::parse($a->check_in)->diffInDays(\Carbon\Carbon::parse($a->check_out))
                        : '--';
                @endphp
                <tr>
                    <td class="font-mono text-muted">{{ $idx + 1 }}</td>
                    <td class="font-weight-bold">{{ $a->family_name }}, {{ $a->first_name }}</td>
                    <td class="font-mono">{{ \App\Models\Passenger::maskPassport($a->passport_no) }}</td>
                    <td>{{ $a->gender ?? '--' }}</td>
                    <td class="font-mono">{{ $a->room_number }}</td>
                    <td class="text-capitalize">{{ $a->room_type ?? '--' }}</td>
                    <td class="text-center font-mono">{{ $a->capacity }}</td>
                    <td class="font-mono">{{ $a->check_in ? \Carbon\Carbon::parse($a->check_in)->format('d M Y') : '--' }}</td>
                    <td class="font-mono">{{ $a->check_out ? \Carbon\Carbon::parse($a->check_out)->format('d M Y') : '--' }}</td>
                    <td class="font-mono text-center">{{ $nights }}</td>
                    <td class="text-muted">{{ $a->group_name }}</td>
                    <td>
                        <span class="status-{{ $a->allocation_status === 'confirmed' ? 'confirmed' : 'pending' }}">
                            {{ strtoupper($a->allocation_status ?? 'pending') }}
                        </span>
                    </td>
                </tr>
                @endforeach
                @empty
                <tr><td colspan="12" class="text-center text-muted py-4">No {{ $city }} hotel allocations found for this period.</td></tr>
                @endforelse
            </tbody>
            @if(count($allocations) > 0)
            <tfoot>
                <tr style="background:#fafbfe; font-weight:700;">
                    <td colspan="12" class="text-right text-uppercase">Total Allocations: {{ count($allocations) }} across {{ $grouped->count() }} hotels</td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</div>
@endsection
