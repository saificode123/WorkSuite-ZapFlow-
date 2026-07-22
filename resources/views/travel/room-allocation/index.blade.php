@extends('layouts.app')

@push('css')
<style>
/* ── Room Allocation Grid ───────────────────────────────────────────────────── */
.room-grid-wrapper {
    overflow-x: auto;
    padding-bottom: 1rem;
}

.room-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
    gap: 14px;
    min-width: 600px;
}

.room-card {
    background: #fff;
    border: 2px solid #e5e7ef;
    border-radius: 10px;
    overflow: hidden;
    transition: border-color .2s, box-shadow .2s;
    min-height: 160px;
    display: flex;
    flex-direction: column;
}

.room-card.drag-over {
    border-color: #0d6efd;
    box-shadow: 0 0 0 3px rgba(13,110,253,.15);
}

.room-card.full { border-color: #dc3545; opacity: .85; }
.room-card.available { border-color: #198754; }
.room-card.partial { border-color: #ffc107; }

.room-card-header {
    background: #f4f6fb;
    padding: 8px 12px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    border-bottom: 1px solid #eee;
}

.room-card-header .room-no {
    font-weight: 700;
    font-size: 14px;
    color: #333;
}

.room-card-header .room-type-badge {
    font-size: 10px;
    background: #e9ecef;
    border-radius: 20px;
    padding: 2px 8px;
    color: #555;
}

.room-card-body {
    padding: 8px;
    flex: 1;
    min-height: 80px;
}

.occupant-chip {
    display: flex;
    align-items: center;
    gap: 6px;
    background: #f0f4ff;
    border-radius: 6px;
    padding: 4px 8px;
    margin-bottom: 4px;
    font-size: 12px;
    cursor: grab;
    border: 1px solid transparent;
    transition: background .15s;
}

.occupant-chip:hover { background: #dce8ff; border-color: #0d6efd; }
.occupant-chip.dragging { opacity: .4; }

.occupant-chip .gender-icon { font-size: 11px; }
.occupant-chip .pax-name { flex: 1; font-weight: 500; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.occupant-chip .remove-btn {
    background: none; border: none; color: #dc3545; font-size: 13px; padding: 0;
    cursor: pointer; line-height: 1;
}

.room-drop-target {
    border: 2px dashed #ccc;
    border-radius: 6px;
    padding: 6px;
    text-align: center;
    color: #aaa;
    font-size: 11px;
    min-height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-top: 4px;
}

.room-drop-target.drag-over { border-color: #0d6efd; color: #0d6efd; background: #eef2ff; }

.room-card-footer {
    padding: 4px 10px 8px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.capacity-bar {
    height: 4px;
    background: #e9ecef;
    border-radius: 4px;
    flex: 1;
    margin-right: 8px;
    overflow: hidden;
}

.capacity-bar-fill {
    height: 100%;
    border-radius: 4px;
    background: #198754;
    transition: width .3s;
}

.capacity-bar-fill.warn { background: #ffc107; }
.capacity-bar-fill.full { background: #dc3545; }

/* Unassigned pool */
.unassigned-pool {
    background: #f8f9fd;
    border: 1px solid #e5e7ef;
    border-radius: 10px;
    padding: 12px;
    max-height: 320px;
    overflow-y: auto;
}

.unassigned-chip {
    display: flex;
    align-items: center;
    gap: 6px;
    background: #fff;
    border: 1px dashed #bbb;
    border-radius: 6px;
    padding: 6px 10px;
    margin-bottom: 6px;
    font-size: 12px;
    cursor: grab;
    transition: border-color .15s, box-shadow .15s;
}

.unassigned-chip:hover { border-color: #0d6efd; box-shadow: 0 1px 6px rgba(13,110,253,.15); }
.unassigned-chip.dragging { opacity: .4; }
</style>
@endpush

@section('content')
<div class="content-wrapper">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0">@lang('app.roomAllocation')</h4>
            @if($bookingGroup)
                <small class="text-muted">{{ $bookingGroup->group_name }}
                    &mdash; {{ $assignedCount }}/{{ $totalPassengers }} @lang('modules.room.assigned')
                </small>
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
            <div class="font-weight-bold mb-2 small text-uppercase text-muted">
                @lang('modules.room.unassignedPassengers') ({{ $unassignedPassengers->count() }})
            </div>
            <div class="unassigned-pool" id="unassigned-pool">
                @foreach($unassignedPassengers as $pax)
                <div class="unassigned-chip"
                     draggable="true"
                     data-passenger-id="{{ $pax->id }}"
                     data-gender="{{ $pax->gender }}">
                    <span class="gender-icon">{{ $pax->gender === 'Male' ? '♂' : '♀' }}</span>
                    <span class="pax-name">{{ $pax->full_name }}</span>
                </div>
                @endforeach
                @if($unassignedPassengers->isEmpty())
                    <div class="text-center text-muted py-3 small">
                        <i class="fa fa-check-circle text-success fa-2x mb-2 d-block"></i>
                        @lang('modules.room.allAssigned')
                    </div>
                @endif
            </div>
        </div>

        {{-- Right: Room Grid --}}
        <div class="col-lg-9 col-md-8">
            <div class="font-weight-bold mb-2 small text-uppercase text-muted">
                @lang('modules.room.hotelRooms') ({{ $hotel->name ?? '' }})
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
                            <span class="room-no">{{ $room->room_number ?: 'Room '.$room->id }}</span>
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
                                <span class="gender-icon">{{ $alloc->passenger?->gender === 'Male' ? '♂' : '♀' }}</span>
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
                            <small class="text-muted">{{ $occupied }}/{{ $room->capacity }}</small>
                        </div>
                    </div>
                    @endforeach

                    @if($rooms->isEmpty())
                    <div class="col-12 text-center text-muted py-5">
                        <i class="fa fa-bed fa-3x mb-3 d-block"></i>
                        @lang('modules.room.noRoomsForHotel')
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @else
    <div class="text-center py-5 text-muted">
        <i class="fa fa-hotel fa-3x mb-3 d-block"></i>
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
