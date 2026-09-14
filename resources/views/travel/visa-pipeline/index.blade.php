@extends('layouts.app')

@push('datatable-styles')
    @include('sections.datatable_css')
    <style>
        /* ── Visa Pipeline Kanban ─────────────────────────────────────── */
        .visa-kanban-board {
            display: flex;
            gap: 14px;
            overflow-x: auto;
            padding-bottom: 1rem;
            min-height: 70vh;
        }

        .visa-column {
            flex: 0 0 220px;
            background: #f8f9fd;
            border-radius: 10px;
            border: 1px solid #e5e7ef;
            display: flex;
            flex-direction: column;
            transition: box-shadow .15s ease;
        }

        .visa-column:hover {
            box-shadow: 0 2px 10px rgba(0,0,0,.06);
        }

        .visa-column-header {
            padding: 12px 14px;
            font-weight: 600;
            font-size: 13px;
            border-radius: 10px 10px 0 0;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 2;
        }

        .visa-column-body {
            padding: 10px;
            flex: 1;
            min-height: 120px;
            overflow-y: auto;
        }

        .visa-card {
            background: #fff;
            border-radius: 8px;
            padding: 10px 12px;
            margin-bottom: 8px;
            box-shadow: 0 1px 4px rgba(0,0,0,.08);
            cursor: grab;
            border-left: 3px solid transparent;
            transition: box-shadow .2s ease, transform .15s ease;
        }

        .visa-card:hover { box-shadow: 0 3px 10px rgba(0,0,0,.15); transform: translateY(-1px); }
        .visa-card:active { cursor: grabbing; }
        .visa-card.dragging { opacity: .5; transform: rotate(2deg); }
        .visa-column-body.drag-over { background: #eef2ff; border-radius: 0 0 10px 10px; }

        .visa-card .passport-no  { font-size: 11px; color: #888; font-family: monospace; }
        .visa-card .pax-name     { font-weight: 600; font-size: 13px; color: #222; }
        .visa-card .mofa-badge   { font-size: 10px; display: inline-block; padding: 2px 8px; border-radius: 20px; }

        /* Empty-column placeholder — purely visual, see script note */
        .visa-empty-state {
            text-align: center;
            color: #b3b6c2;
            font-size: 12px;
            padding: 24px 8px;
        }

        /* Column colours */
        .col-draft           .visa-column-header { background: #6c757d; }
        .col-sent_to_embassy .visa-column-header { background: #0d6efd; }
        .col-mofa_received   .visa-column-header { background: #6610f2; }
        .col-issued          .visa-column-header { background: #198754; }
        .col-rejected        .visa-column-header { background: #dc3545; }

        .col-draft           .visa-card { border-left-color: #6c757d; }
        .col-sent_to_embassy .visa-card { border-left-color: #0d6efd; }
        .col-mofa_received   .visa-card { border-left-color: #6610f2; }
        .col-issued          .visa-card { border-left-color: #198754; }
        .col-rejected        .visa-card { border-left-color: #dc3545; }

        .count-badge {
            background: rgba(255,255,255,.25);
            border-radius: 20px;
            padding: 1px 8px;
            font-size: 11px;
        }

        .ct-header-icon {
            width: 34px;
            height: 34px;
            border-radius: 8px;
            background: rgba(0, 123, 255, .08);
            color: #007bff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            flex-shrink: 0;
        }
    </style>
@endpush

@section('filter-section')
    <x-filters.filter-box>
        <div class="select-box d-flex py-1 px-2 pr-lg-0">
            <p class="mb-0 pr-2 pt-2 text-dark-grey d-flex align-items-center">@lang('app.booking')</p>
            <x-forms.select fieldId="filterBookingGroup" class="select-picker" fieldName="filterBookingGroup">
                <option value="">-- @lang('app.all') --</option>
                @foreach($bookingGroups as $bg)
                    <option value="{{ $bg->id }}" @selected($selectedBookingId == $bg->id)>{{ $bg->group_name }}</option>
                @endforeach
            </x-forms.select>
        </div>
    </x-filters.filter-box>
@endsection

@section('content')
    <div class="content-wrapper">

        {{-- Header --}}
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="d-flex align-items-center">
                <div class="ct-header-icon mr-3">
                    <i class="fa fa-passport"></i>
                </div>
                <div>
                    <h4 class="mb-0 f-21 font-weight-normal">@lang('app.visaPipeline')</h4>
                    <small class="text-muted">
                        @if($selectedBooking)
                            {{ $selectedBooking->group_name }} &mdash; {{ $passengers->count() }} {{ __('app.passengers') }}
                        @else
                            @lang('modules.visa.selectGroupToFilter')
                        @endif
                    </small>
                </div>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-sm btn-outline-secondary" id="btn-expand-all" title="@lang('app.expandAll')">
                    <i class="fa fa-expand-alt"></i> @lang('app.expandAll')
                </button>
                <button class="btn btn-sm btn-success" id="btn-bulk-move" style="display:none">
                    <i class="fa fa-arrows-alt"></i> @lang('modules.visa.bulkMove')
                </button>
            </div>
        </div>

        {{-- Kanban Board --}}
        <div class="visa-kanban-board" id="visa-kanban">
            @foreach($columns as $statusKey => $label)
            <div class="visa-column col-{{ $statusKey }}" data-status="{{ $statusKey }}">
                <div class="visa-column-header">
                    <span>{{ $label }}</span>
                    <span class="count-badge count-{{ $statusKey }}">{{ $passengers->where('visa_pipeline_status', $statusKey)->count() }}</span>
                </div>
                <div class="visa-column-body" id="col-{{ $statusKey }}">
                    @foreach($passengers->where('visa_pipeline_status', $statusKey) as $passenger)
                    <div class="visa-card"
                         draggable="true"
                         data-passenger-id="{{ $passenger->id }}"
                         data-current-status="{{ $passenger->visa_pipeline_status }}"
                         data-mofa="{{ $passenger->mofa_status }}">
                        <div class="pax-name">{{ $passenger->full_name }}</div>
                        <div class="passport-no">{{ $passenger->masked_passport_no }}</div>
                        <div class="d-flex align-items-center justify-content-between mt-1">
                            <span class="text-muted" style="font-size:11px">
                                {{ $passenger->gender === 'Male' ? '♂' : '♀' }}
                                {{ $passenger->birth_date ? \Carbon\Carbon::parse($passenger->birth_date)->age . 'y' : '' }}
                            </span>
                            @if($passenger->visa_mofa_ref)
                                <span class="mofa-badge bg-light text-secondary">MoFA: {{ $passenger->visa_mofa_ref }}</span>
                            @endif
                            <div class="dropdown">
                                <button class="btn btn-sm btn-light py-0 px-1" data-toggle="dropdown" aria-label="@lang('app.action')">
                                    <i class="fa fa-ellipsis-v"></i>
                                </button>
                                <div class="dropdown-menu dropdown-menu-right shadow-sm">
                                    @foreach($columns as $sk => $sl)
                                        @if($sk !== $passenger->visa_pipeline_status)
                                        <a href="javascript:;" class="dropdown-item move-to-status"
                                           data-passenger-id="{{ $passenger->id }}"
                                           data-status="{{ $sk }}">
                                            → {{ $sl }}
                                        </a>
                                        @endif
                                    @endforeach
                                    <div class="dropdown-divider"></div>
                                    <a href="javascript:;" class="dropdown-item set-mofa-ref"
                                       data-passenger-id="{{ $passenger->id }}"
                                       data-mofa="{{ $passenger->visa_mofa_ref }}">
                                        <i class="fa fa-tag"></i> @lang('modules.visa.setMofaRef')
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                    @if($passengers->where('visa_pipeline_status', $statusKey)->count() === 0)
                        <div class="visa-empty-state">@lang('messages.noRecordFound')</div>
                    @endif
                </div>
            </div>
            @endforeach
        </div>

    </div>

    {{-- MoFA Ref Modal --}}
    <div class="modal fade" id="mofaRefModal" tabindex="-1">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">@lang('modules.visa.setMofaRef')</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="mofa-passenger-id">
                    <div class="form-group">
                        <label>@lang('modules.visa.mofaReferenceNo')</label>
                        <input type="text" id="mofa-ref-value" class="form-control" placeholder="e.g. MF-2024-001234">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary btn-sm" id="save-mofa-ref">@lang('app.save')</button>
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">@lang('app.cancel')</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Rejection Reason Modal --}}
    <div class="modal fade" id="rejectionModal" tabindex="-1">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">@lang('modules.visa.rejectionReason')</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="rejection-passenger-id">
                    <div class="form-group">
                        <label>@lang('modules.visa.reason')</label>
                        <textarea id="rejection-reason-value" class="form-control" rows="3"
                                  placeholder="@lang('modules.visa.reasonPlaceholder')"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger btn-sm" id="save-rejection">@lang('app.save')</button>
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">@lang('app.cancel')</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
'use strict';

// ── Drag & Drop ───────────────────────────────────────────────────────────────
let draggedCard = null;

document.querySelectorAll('.visa-card').forEach(card => {
    card.addEventListener('dragstart', e => {
        draggedCard = card;
        card.classList.add('dragging');
        e.dataTransfer.effectAllowed = 'move';
    });
    card.addEventListener('dragend', () => {
        draggedCard?.classList.remove('dragging');
        draggedCard = null;
        document.querySelectorAll('.visa-column-body').forEach(b => b.classList.remove('drag-over'));
    });
});

document.querySelectorAll('.visa-column-body').forEach(col => {
    col.addEventListener('dragover', e => {
        e.preventDefault();
        col.classList.add('drag-over');
    });
    col.addEventListener('dragleave', () => col.classList.remove('drag-over'));
    col.addEventListener('drop', e => {
        e.preventDefault();
        col.classList.remove('drag-over');
        if (!draggedCard) return;

        const newStatus   = col.closest('.visa-column').dataset.status;
        const passengerId = draggedCard.dataset.passengerId;
        const oldStatus   = draggedCard.dataset.currentStatus;

        if (newStatus === oldStatus) return;

        // Optimistic UI move
        col.appendChild(draggedCard);
        draggedCard.dataset.currentStatus = newStatus;
        updateCounts();

        if (newStatus === 'rejected') {
            pendingPassengerId = passengerId;
            pendingNewStatus   = newStatus;
            $('#rejectionModal').modal('show');
        } else {
            movePassenger(passengerId, newStatus, null, null, oldStatus);
        }
    });
});

// ── Dropdown "Move to" ────────────────────────────────────────────────────────
$('body').on('click', '.move-to-status', function() {
    const passengerId = $(this).data('passenger-id');
    const newStatus   = $(this).data('status');
    const card        = $(`.visa-card[data-passenger-id="${passengerId}"]`);
    const oldStatus   = card.data('current-status');

    if (newStatus === 'rejected') {
        pendingPassengerId = passengerId;
        pendingNewStatus   = newStatus;
        pendingCard        = card[0];
        $('#rejectionModal').modal('show');
        return;
    }

    $(`#col-${newStatus}`).append(card);
    card.attr('data-current-status', newStatus);
    updateCounts();
    movePassenger(passengerId, newStatus, null, null, oldStatus);
});

// ── AJAX Move ─────────────────────────────────────────────────────────────────
let pendingPassengerId = null;
let pendingNewStatus   = null;
let pendingCard        = null;

function movePassenger(passengerId, newStatus, mofaRef, rejectionReason, oldStatus) {
    $.easyAjax({
        url: '{{ route("visa-pipeline.move") }}',
        type: 'POST',
        data: {
            _token: '{{ csrf_token() }}',
            passenger_id: passengerId,
            new_status: newStatus,
            mofa_ref: mofaRef,
            rejection_reason: rejectionReason,
        },
        success: function(response) {
            if (response.status !== 'success') {
                // Revert on failure
                if (oldStatus) {
                    const card = $(`.visa-card[data-passenger-id="${passengerId}"]`);
                    $(`#col-${oldStatus}`).append(card);
                    card.attr('data-current-status', oldStatus);
                    updateCounts();
                }
                window.toastr.error(response.message);
            }
        }
    });
}

$('#save-mofa-ref').click(function() {
    const passengerId = $('#mofa-passenger-id').val();
    const mofaRef     = $('#mofa-ref-value').val().trim();
    if (!mofaRef) return;

    $.easyAjax({
        url: '{{ route("visa-pipeline.mofa-ref") }}',
        type: 'POST',
        data: { _token: '{{ csrf_token() }}', passenger_id: passengerId, mofa_ref: mofaRef },
        success: function(response) {
            if (response.status === 'success') {
                const card = $(`.visa-card[data-passenger-id="${passengerId}"]`);
                card.find('.mofa-badge').remove();
                card.find('.d-flex').prepend(`<span class="mofa-badge bg-light text-secondary">MoFA: ${mofaRef}</span>`);
                card.attr('data-mofa', mofaRef);
                $('#mofaRefModal').modal('hide');
            }
        }
    });
});

$('#save-rejection').click(function() {
    const reason = $('#rejection-reason-value').val().trim();
    if (pendingPassengerId && pendingNewStatus) {
        const card = pendingCard ?? $(`.visa-card[data-passenger-id="${pendingPassengerId}"]`)[0];
        if (card) {
            $(`#col-${pendingNewStatus}`).append(card);
            $(card).attr('data-current-status', pendingNewStatus);
            updateCounts();
        }
        movePassenger(pendingPassengerId, pendingNewStatus, null, reason, null);
        pendingPassengerId = null;
        pendingNewStatus   = null;
        pendingCard        = null;
    }
    $('#rejectionModal').modal('hide');
    $('#rejection-reason-value').val('');
});

$('body').on('click', '.set-mofa-ref', function() {
    $('#mofa-passenger-id').val($(this).data('passenger-id'));
    $('#mofa-ref-value').val($(this).data('mofa') || '');
    $('#mofaRefModal').modal('show');
});

// ── Booking group filter ──────────────────────────────────────────────────────
$('#filterBookingGroup').on('change', function() {
    const id = $(this).val();
    window.location.href = '{{ route("visa-pipeline.index") }}' + (id ? '?booking_group_id=' + id : '');
});

// ── Real-time Echo: update kanban when another user moves a passenger ──────────
(function () {
    const companyId = window.ZapFlowCompanyId || null;
    if (!companyId || !window.Echo) return;

    window.Echo.private(`visa-pipeline.${companyId}`)
        .listen('.visa.status-changed', function (e) {
            const card = document.querySelector(`.visa-card[data-passenger-id="${e.passenger_id}"]`);
            if (!card) return; // Not on this view
            // Only move if this card is NOT the one that was just dragged (movePassenger handles that).
            const wasJustMoved = card.dataset._justMoved;
            if (wasJustMoved) {
                delete card.dataset._justMoved;
                return;
            }
            // An external update — move the card to the new column.
            const oldCol = card.closest('.visa-column-body');
            const newCol = document.getElementById(`col-${e.to_status}`);
            if (newCol && oldCol && newCol !== oldCol) {
                newCol.appendChild(card);
                card.dataset.currentStatus = e.to_status;
                updateCounts();
            }
        });

    // Mark cards just moved locally so the Echo handler skips them.
    document.querySelectorAll('.visa-column-body').forEach(col => {
        col.addEventListener('dragend', function () {
            if (draggedCard) draggedCard.dataset._justMoved = '1';
        });
    });
})();
    document.querySelectorAll('.visa-column').forEach(col => {
        const status = col.dataset.status;
        const count  = col.querySelectorAll('.visa-card').length;
        col.querySelector('.count-badge').textContent = count;

        // Cosmetic addition: toggle the empty-state placeholder to match.
        // Purely visual — doesn't affect move logic or AJAX calls above.
        const body = col.querySelector('.visa-column-body');
        let empty  = body.querySelector('.visa-empty-state');
        if (count === 0 && !empty) {
            empty = document.createElement('div');
            empty.className = 'visa-empty-state';
            empty.textContent = '{{ __('messages.noRecordFound') }}';
            body.appendChild(empty);
        } else if (count > 0 && empty) {
            empty.remove();
        }
    });
}
</script>
@endpush