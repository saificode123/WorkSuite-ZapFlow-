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
.font-mono { font-family:'IBM Plex Mono',monospace; }
.mofa-approved { background:#e7f8f1; color:#17a673; padding:2px 7px; border-radius:8px; font-size:11px; font-weight:600; }
.mofa-pending { background:#fef4e2; color:#e8960c; padding:2px 7px; border-radius:8px; font-size:11px; font-weight:600; }
</style>
@endpush

@section('content')
<div class="content-wrapper rpt-box">
    <div class="rpt-mast">
        <div>
            <h1><i class="fa fa-file-alt mr-2 text-success"></i>KSA Intimation Report</h1>
            <p class="text-muted mb-0">Arrival Notification Manifest — Passenger Listing for Saudi Border Control</p>
        </div>
        <div>
            <button onclick="window.print()" class="btn btn-sm btn-outline-dark"><i class="fa fa-print mr-1"></i>Print</button>
        </div>
    </div>

    <div class="rpt-filter">
        <form method="GET" action="{{ route('travel-reports.ksa-intimation') }}" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label text-muted font-weight-bold" style="font-size:12px;">Departure From</label>
                <input type="date" name="from_date" class="form-control form-control-sm font-mono" value="{{ request('from_date', $fromDate) }}">
            </div>
            <div class="col-md-3">
                <label class="form-label text-muted font-weight-bold" style="font-size:12px;">Departure To</label>
                <input type="date" name="to_date" class="form-control form-control-sm font-mono" value="{{ request('to_date', $toDate) }}">
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-sm btn-primary"><i class="fa fa-filter mr-1"></i>Generate</button>
                <a href="{{ route('travel-reports.ksa-intimation') }}" class="btn btn-sm btn-light border ml-1"><i class="fa fa-times mr-1"></i>Reset</a>
            </div>
        </form>
    </div>

    <div class="table-responsive">
        <table class="rpt-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Family Name</th>
                    <th>First Name</th>
                    <th>Passport No</th>
                    <th>Date of Birth</th>
                    <th>Gender</th>
                    <th>Relation</th>
                    <th>MOFA Status</th>
                    <th>Group Ref</th>
                    <th>Departure</th>
                    <th>Return</th>
                    <th>Package</th>
                </tr>
            </thead>
            <tbody>
                @forelse($passengers as $idx => $p)
                <tr>
                    <td class="font-mono text-muted">{{ $idx + 1 }}</td>
                    <td class="font-weight-bold">{{ strtoupper($p->family_name) }}</td>
                    <td>{{ $p->first_name }}</td>
                    <td class="font-mono">{{ \App\Models\Passenger::maskPassport($p->passport_no) }}</td>
                    <td class="font-mono">{{ $p->birth_date ? \Carbon\Carbon::parse($p->birth_date)->format('d/m/Y') : '--' }}</td>
                    <td>{{ $p->gender ?? '--' }}</td>
                    <td>{{ $p->relation_name ?? '--' }}</td>
                    <td>
                        @if($p->mofa_status && $p->mofa_status !== '0')
                            <span class="mofa-approved">APPROVED</span>
                        @else
                            <span class="mofa-pending">PENDING</span>
                        @endif
                    </td>
                    <td class="font-mono text-muted">{{ $p->group_no ?: ('BG-' . $p->group_name) }}</td>
                    <td class="font-mono">{{ $p->departure_date ? \Carbon\Carbon::parse($p->departure_date)->format('d M Y') : '--' }}</td>
                    <td class="font-mono">{{ $p->return_date ? \Carbon\Carbon::parse($p->return_date)->format('d M Y') : '--' }}</td>
                    <td>{{ $p->package_name ?? 'Custom' }}</td>
                </tr>
                @empty
                <tr><td colspan="12" class="text-center text-muted py-4">No passengers found for this date range.</td></tr>
                @endforelse
            </tbody>
            @if(count($passengers) > 0)
            <tfoot>
                <tr style="background:#fafbfe; font-weight:700;">
                    <td colspan="7" class="text-right text-uppercase">Total Passengers:</td>
                    <td colspan="5" class="font-mono">{{ count($passengers) }}</td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</div>
@endsection
