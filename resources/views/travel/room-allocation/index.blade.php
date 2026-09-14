@extends('layouts.app')

@push('css')
<style>
/* ── Design tokens ──────────────────────────────────────────────────────────── */
.content-wrapper {
    --ra-bg:        #f6f7fb;
    --ra-surface:   #ffffff;
    --ra-border:    #e7e9f2;
    --ra-text:      #1f2430;
    --ra-muted:     #7a8194;
    --ra-primary:   #4f6ef7;
    --ra-primary-soft: #eef1ff;
    --ra-success:   #17a673;
    --ra-success-soft: #e7f8f1;
    --ra-warning:   #e8960c;
    --ra-warning-soft: #fef4e2;
    --ra-danger:    #e2493d;
    --ra-danger-soft: #fdece9;
    --ra-radius:    12px;
    --ra-shadow:    0 1px 2px rgba(20,24,40,.04), 0 6px 16px -8px rgba(20,24,40,.10);
}

/* ── Page header ────────────────────────────────────────────────────────────── */
.ra-page-title {
    font-weight: 700;
    letter-spacing: -.01em;
    color: var(--ra-text);
}

.ra-page-subtitle {
    color: var(--ra-muted);
    font-size: 13px;
}

.ra-progress-pill {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: var(--ra-primary-soft);
    color: var(--ra-primary);
    border-radius: 20px;
    padding: 2px 10px 2px 8px;
    font-weight: 600;
    font-size: 12px;
    margin-left: 8px;
}

.ra-progress-pill .dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: var(--ra-primary);
    display: inline-block;
}

#filterBooking.select-picker {
    border-radius: 8px;
    border-color: var(--ra-border);
    box-shadow: none;
}

#btn-save-all {
    border-radius: 8px;
    font-weight: 600;
    box-shadow: var(--ra-shadow);
}

/* ── Section labels ─────────────────────────────────────────────────────────── */
.ra-section-label {
    font-weight: 700;
    font-size: 11px;
    letter-spacing: .06em;
    text-transform: uppercase;
    color: var(--ra-muted);
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 10px;
}

.ra-section-label .count-badge {
    background: var(--ra-border);
    color: var(--ra-text);
    border-radius: 20px;
    padding: 1px 8px;
    font-size: 10px;
    letter-spacing: 0;
}

/* ── Room Allocation Grid ───────────────────────────────────────────────────── */
.room-grid-wrapper {
    overflow-x: auto;
    padding-bottom: 4px;
}

.room-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(190px, 1fr));
    gap: 16px;
    min-width: 600px;
}

.room-card {
    background: var(--ra-surface);
    border: 1px solid var(--ra-border);
    border-radius: var(--ra-radius);
    overflow: hidden;
    box-shadow: var(--ra-shadow);
    transition: border-color .18s ease, box-shadow .18s ease, transform .18s ease;
    min-height: 168px;
    display: flex;
    flex-direction: column;
    position: relative;
}

.room-card::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 3px;
    background: var(--ra-border);
}

.room-card.available::before { background: var(--ra-success); }
.room-card.partial::before   { background: var(--ra-warning); }
.room-card.full::before      { background: var(--ra-danger); }

.room-card.drag-over {
    border-color: var(--ra-primary);
    box-shadow: 0 0 0 3px var(--ra-primary-soft), var(--ra-shadow);
    transform: translateY(-1px);
}

.room-card.full { opacity: .92; }

.room-card-header {
    background: transparent;
    padding: 12px 12px 8px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    border-bottom: 1px solid var(--ra-border);
}

.room-card-header .room-no {
    font-weight: 700;
    font-size: 14px;
    color: var(--ra-text);
    display: flex;
    align-items: center;
    gap: 6px;
}

.room-card-header .room-no i {
    color: var(--ra-muted);
    font-size: 12px;
}

.room-card-header .room-type-badge {
    font-size: 10px;
    font-weight: 600;
    letter-spacing: .03em;
    text-transform: uppercase;
    background: var(--ra-primary-soft);
    border-radius: 20px;
    padding: 3px 9px;
    color: var(--ra-primary);
}

.room-card-body {
    padding: 10px;
    flex: 1;
    min-height: 80px;
    display: flex;
    flex-direction: column;
}

.occupant-chip {
    display: flex;
    align-items: center;
    gap: 7px;
    background: var(--ra-primary-soft);
    border-radius: 7px;
    padding: 6px 8px;
    margin-bottom: 5px;
    font-size: 12.5px;
    cursor: grab;
    border: 1px solid transparent;
    transition: background .15s ease, border-color .15s ease, transform .1s ease;
}

.occupant-chip:hover { background: #e2e8ff; border-color: var(--ra-primary); transform: translateX(1px); }
.occupant-chip:active { cursor: grabbing; }
.occupant-chip.dragging { opacity: .4; }

.occupant-chip .gender-icon {
    font-size: 11px;
    width: 16px;
    height: 16px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    flex-shrink: 0;
}
.occupant-chip .gender-icon.male   { background: #dbe7ff; color: #3462d1; }
.occupant-chip .gender-icon.female { background: #ffe0ec; color: #d13478; }

.occupant-chip .pax-name { flex: 1; font-weight: 500; color: var(--ra-text); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.occupant-chip .remove-btn {
    background: none; border: none; color: var(--ra-danger); font-size: 15px; padding: 0 2px;
    cursor: pointer; line-height: 1; border-radius: 4px; opacity: .55;
    transition: opacity .15s ease, background .15s ease;
}
.occupant-chip .remove-btn:hover { opacity: 1; background: var(--ra-danger-soft); }

.room-drop-target {
    border: 1.5px dashed var(--ra-border);
    border-radius: 8px;
    padding: 8px;
    text-align: center;
    color: #aeb4c4;
    font-size: 11px;
    font-weight: 500;
    min-height: 38px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-top: auto;
    transition: border-color .15s ease, color .15s ease, background .15s ease;
}

.room-drop-target.drag-over { border-color: var(--ra-primary); color: var(--ra-primary); background: var(--ra-primary-soft); }

.room-card-footer {
    padding: 6px 12px 12px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.capacity-bar {
    height: 5px;
    background: var(--ra-border);
    border-radius: 4px;
    flex: 1;
    margin-right: 10px;
    overflow: hidden;
}

.capacity-bar-fill {
    height: 100%;
    border-radius: 4px;
    background: var(--ra-success);
    transition: width .3s ease;
}

.capacity-bar-fill.warn { background: var(--ra-warning); }
.capacity-bar-fill.full { background: var(--ra-danger); }

.room-card-footer small {
    font-weight: 600;
    color: var(--ra-muted);
    font-size: 11px;
    white-space: nowrap;
}

/* Unassigned pool */
.unassigned-pool {
    background: var(--ra-surface);
    border: 1px solid var(--ra-border);
    border-radius: var(--ra-radius);
    padding: 12px;
    max-height: 340px;
    overflow-y: auto;
    box-shadow: var(--ra-shadow);
}

.unassigned-pool::-webkit-scrollbar,
.room-grid-wrapper::-webkit-scrollbar { height: 8px; width: 8px; }
.unassigned-pool::-webkit-scrollbar-thumb,
.room-grid-wrapper::-webkit-scrollbar-thumb { background: var(--ra-border); border-radius: 8px; }

.unassigned-chip {
    display: flex;
    align-items: center;
    gap: 7px;
    background: var(--ra-surface);
    border: 1px solid var(--ra-border);
    border-radius: 8px;
    padding: 7px 10px;
    margin-bottom: 7px;
    font-size: 12.5px;
    cursor: grab;
    transition: border-color .15s ease, box-shadow .15s ease, transform .1s ease;
}

.unassigned-chip .gender-icon {
    font-size: 11px;
    width: 16px;
    height: 16px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    flex-shrink: 0;
}
.unassigned-chip .gender-icon.male   { background: #dbe7ff; color: #3462d1; }
.unassigned-chip .gender-icon.female { background: #ffe0ec; color: #d13478; }
.unassigned-chip .pax-name { font-weight: 500; color: var(--ra-text); }

.unassigned-chip:hover { border-color: var(--ra-primary); box-shadow: 0 2px 8px rgba(79,110,247,.15); transform: translateX(1px); }
.unassigned-chip:active { cursor: grabbing; }
.unassigned-chip.dragging { opacity: .4; }

/* Empty states */
.ra-empty-state {
    text-align: center;
    color: var(--ra-muted);
    padding: 34px 12px;
}
.ra-empty-state i { display: block; margin-bottom: 10px; }
.ra-empty-state.success i { color: var(--ra-success); }

.ra-empty-page {
    text-align: center;
    padding: 72px 12px;
    color: var(--ra-muted);
    background: var(--ra-surface);
    border: 1px dashed var(--ra-border);
    border-radius: var(--ra-radius);
}
.ra-empty-page i { color: #c6cbdb; margin-bottom: 14px; display: block; }
.ra-empty-page h5 { color: var(--ra-text); font-weight: 700; }

/* Reduced motion */
@media (prefers-reduced-motion: reduce) {
    .room-card, .occupant-chip, .unassigned-chip, .capacity-bar-fill, .room-drop-target { transition: none; }
}
</style>
@endpush

@section('content')
<div class="content-wrapper">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div>
            <h4 class="mb-0 ra-page-title">@lang('app.roomAllocation')</h4>
            @if($bookingGroup)
                <span class="ra-page-subtitle">{{ $bookingGroup->group_name }}</span>
                <span class="ra-progress-pill"><span class="dot"></span>{{ $assignedCount }}/{{ $totalPassengers }} @lang('modules.room.assigned')</span>
            @endif
        </div>
        <div class="d-flex align-items-center gap-2">
            <select class="form-control form-control-sm select-picker" id="filterBooking" style="min-width:200px">
                <option value="">-- @lang('app.selectBooking') --</option>
                @foreach($bookingGroups as $bg)
                    <option value="{{ $bg->id }}" @selected($bookingGroup && $bookingGroup->id == $bg->id)>
                        {{ $bg->group_name }}
                    </option>
                @endforeach
            </select>
            <button class="btn btn-sm btn-success" id="btn-save-all" style="display:none">
                <i class="fa fa-save"></i> @lang('app.saveAll')
            </button>
        </div>
    </div>

    @if($bookingGroup)
    <div class="row">

        {{-- Left: Unassigned Pool --}}
        <div class="col-lg-3 col-md-4 mb-3">
            <div class="ra-section-label">
                <span>@lang('modules.room.unassignedPassengers')</span>
                <span class="count-badge">{{ $unassignedPassengers->count() }}</span>
            </div>
            <div class="unassigned-pool" id="unassigned-pool">
                @foreach($unassignedPassengers as $pax)
                <div class="unassigned-chip"
                     draggable="true"
                     data-passenger-id="{{ $pax->id }}"
                     data-gender="{{ $pax->gender }}">
                    <span class="gender-icon {{ $pax->gender === 'Male' ? 'male' : 'female' }}">
                        <i class="fa {{ $pax->gender === 'Male' ? 'fa-mars' : 'fa-venus' }}"></i>
                    </span>
                    <span class="pax-name">{{ $pax->full_name }}</span>
                </div>
                @endforeach
                @if($unassignedPassengers->isEmpty())
                    <div class="ra-empty-state success">
                        <i class="fa fa-check-circle fa-2x"></i>
                        @lang('modules.room.allAssigned')
                    </div>
                @endif
            </div>
        </div>

        {{-- Right: Room Grid --}}
        <div class="col-lg-9 col-md-8">
            <div class="ra-section-label">
                <span>@lang('modules.room.hotelRooms') &mdash; {{ $hotel->name ?? '' }}</span>
            </div>
            <div class="room-grid-wrapper">
                <div class="room-grid" id="room-grid">
                    @foreach($rooms as $room)
                    @php
                        $occupied = $room->activeAllocations->count();
                        $fillPct  = $room->capacity > 0 ? round(($occupied / $room->capacity) * 100) : 0;
                        $statusCls= $occupied >= $room->capacity ? 'full' : ($occupied > 0 ? 'partial' : 'available');
                        $barCls   = $occupied >= $room->capacity ? 'full' : ($occupied / $room->capacity > 0.5 ? 'warn' : '');
                    @endphp
                    <div class="room-card {{ $statusCls }}"
                         data-room-id="{{ $room->id }}"
                         data-capacity="{{ $room->capacity }}"
                         data-gender="{{ $room->gender_restriction }}"
                         data-occupied="{{ $occupied }}">

                        <div class="room-card-header">
                            <span class="room-no"><i class="fa fa-door-closed"></i>{{ $room->room_number ?: 'Room '.$room->id }}</span>
                            <span class="room-type-badge">{{ ucfirst($room->room_type) }}</span>
                        </div>

                        <div class="room-card-body" id="room-body-{{ $room->id }}">
                            @foreach($room->activeAllocations as $alloc)
                            <div class="occupant-chip"
                                 draggable="true"
                                 data-passenger-id="{{ $alloc->passenger_id }}"
                                 data-allocation-id="{{ $alloc->id }}"
                                 data-from-room="{{ $room->id }}"
                                 data-gender="{{ $alloc->passenger?->gender }}">
                                <span class="gender-icon {{ $alloc->passenger?->gender === 'Male' ? 'male' : 'female' }}">
                                    <i class="fa {{ $alloc->passenger?->gender === 'Male' ? 'fa-mars' : 'fa-venus' }}"></i>
                                </span>
                                <span class="pax-name">{{ $alloc->passenger?->full_name }}</span>
                                <button class="remove-btn remove-occupant"
                                        data-allocation-id="{{ $alloc->id }}"
                                        data-passenger-id="{{ $alloc->passenger_id }}"
                                        data-room-id="{{ $room->id }}"
                                        title="@lang('app.remove')">
                                    &times;
                                </button>
                            </div>
                            @endforeach

                            @if($occupied < $room->capacity)
                            <div class="room-drop-target" data-room-id="{{ $room->id }}">
                                <span>+ @lang('modules.room.dropHere')</span>
                            </div>
                            @endif
                        </div>

                        <div class="room-card-footer">
                            <div class="capacity-bar">
                                <div class="capacity-bar-fill {{ $barCls }}" style="width:{{ $fillPct }}%"></div>
                            </div>
                            <small>{{ $occupied }}/{{ $room->capacity }}</small>
                        </div>
                    </div>
                    @endforeach

                    @if($rooms->isEmpty())
                    <div class="col-12 ra-empty-state">
                        <i class="fa fa-bed fa-3x"></i>
                        @lang('modules.room.noRoomsForHotel')
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @else
    <div class="ra-empty-page">
        <i class="fa fa-hotel fa-3x"></i>
        <h5>@lang('modules.room.selectBookingToStart')</h5>
    </div>
    @endif

</div>
@endsection

@push('scripts')
<script>
'use strict';

// ── Booking filter ─────────────────────────────────────────────────────────────
$('#filterBooking').on('change', function() {
    const id = $(this).val();
    if (id) window.location.href = '{{ route("room-allocation.index") }}?booking_group_id=' + id;
});

// ── Drag from unassigned pool ──────────────────────────────────────────────────
let dragPassengerId = null;
let dragFromRoom    = null;
let dragAllocId     = null;

function initDraggables() {
    document.querySelectorAll('.unassigned-chip, .occupant-chip').forEach(chip => {
        chip.addEventListener('dragstart', e => {
            dragPassengerId = chip.dataset.passengerId;
            dragFromRoom    = chip.dataset.fromRoom || null;
            dragAllocId     = chip.dataset.allocationId || null;
            chip.classList.add('dragging');
            e.dataTransfer.effectAllowed = 'move';
        });
        chip.addEventListener('dragend', () => {
            chip.classList.remove('dragging');
            document.querySelectorAll('.room-drop-target, .room-card').forEach(t => t.classList.remove('drag-over'));
        });
    });

    document.querySelectorAll('.room-drop-target, .room-card-body').forEach(target => {
        target.addEventListener('dragover', e => {
            e.preventDefault();
            target.classList.add('drag-over');
        });
        target.addEventListener('dragleave', () => target.classList.remove('drag-over'));
        target.addEventListener('drop', e => {
            e.preventDefault();
            target.classList.remove('drag-over');

            const roomId   = target.closest('[data-room-id]')?.dataset.roomId;
            const roomCard = document.querySelector(`.room-card[data-room-id="${roomId}"]`);
            const capacity = parseInt(roomCard?.dataset.capacity || 1);
            const occupied = parseInt(roomCard?.dataset.occupied || 0);

            if (!roomId || !dragPassengerId) return;
            if (occupied >= capacity) {
                window.toastr.warning('@lang("modules.room.roomFull")');
                return;
            }

            assignPassenger(dragPassengerId, roomId, dragAllocId);
        });
    });
}
initDraggables();

// ── AJAX assign ───────────────────────────────────────────────────────────────
function assignPassenger(passengerId, roomId, oldAllocId) {
    $.easyAjax({
        url: '{{ route("room-allocation.assign") }}',
        type: 'POST',
        data: {
            _token: '{{ csrf_token() }}',
            passenger_id: passengerId,
            room_id: roomId,
            old_allocation_id: oldAllocId,
            booking_group_id: '{{ $bookingGroup?->id }}',
        },
        success: function(response) {
            if (response.status === 'success') {
                // Reload to reflect updated state
                window.location.reload();
            } else {
                window.toastr.error(response.message);
            }
        }
    });
}

// ── Remove occupant ───────────────────────────────────────────────────────────
$('body').on('click', '.remove-occupant', function(e) {
    e.stopPropagation();
    const allocId     = $(this).data('allocation-id');
    const passengerId = $(this).data('passenger-id');
    const roomId      = $(this).data('room-id');

    Swal.fire({
        title: "@lang('messages.sweetAlertTitle')",
        text: "@lang('modules.room.confirmRemove')",
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: "@lang('app.remove')",
        cancelButtonText: "@lang('app.cancel')",
        customClass: { confirmButton: 'btn btn-danger mr-3', cancelButton: 'btn btn-secondary' },
        buttonsStyling: false,
    }).then(result => {
        if (result.isConfirmed) {
            $.easyAjax({
                url: '{{ route("room-allocation.remove") }}',
                type: 'POST',
                data: { _token: '{{ csrf_token() }}', allocation_id: allocId, passenger_id: passengerId },
                success: function(response) {
                    if (response.status === 'success') window.location.reload();
                }
            });
        }
    });
});
</script>
@endpush