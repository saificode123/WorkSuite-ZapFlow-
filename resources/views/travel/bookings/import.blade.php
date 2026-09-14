@extends('layouts.app')

@push('css')
<style>
.import-card {
    background: #fff;
    border-radius: 14px;
    box-shadow: 0 2px 16px rgba(0,0,0,.08);
    overflow: hidden;
    animation: fadeInUp .35s ease;
}
@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(10px); }
    to   { opacity: 1; transform: translateY(0); }
}
.import-header {
    background: linear-gradient(135deg, #1a1f3c, #2d3561);
    color: #fff;
    padding: 24px 28px;
}
.import-header h4 { margin: 0; font-size: 18px; }
.import-header p  { opacity: .7; margin: 4px 0 0; font-size: 13px; }

.upload-zone {
    border: 2.5px dashed #c7d0e8;
    border-radius: 10px;
    padding: 36px;
    text-align: center;
    cursor: pointer;
    transition: border-color .2s, background .2s;
    background: #f8f9fd;
}
.upload-zone:hover, .upload-zone.drag-over {
    border-color: #4f5ed4;
    background: #eef0ff;
}
.upload-zone .icon { font-size: 42px; color: #7b8ac8; margin-bottom: 12px; }
.upload-zone .icon i { display: inline-block; transition: transform .2s ease; }
.upload-zone:hover .icon i, .upload-zone.drag-over .icon i { transform: scale(1.08); }
.upload-zone .hint { font-size: 12px; color: #999; margin-top: 8px; }

/* Step indicator */
.step-indicator {
    display: flex; align-items: stretch; gap: 0;
    margin-bottom: 24px;
    border-radius: 8px;
    overflow: hidden;
}
.step {
    flex: 1;
    display: flex; align-items: center; justify-content: center; gap: 8px;
    padding: 12px 10px;
    background: #f0f2f8;
    font-size: 12px; font-weight: 600; color: #888;
    border-right: 2px solid #fff;
}
.step:last-child { border-right: none; }
.step-badge {
    display: inline-flex; align-items: center; justify-content: center;
    flex-shrink: 0;
    width: 20px; height: 20px;
    border-radius: 50%;
    background: #d7dcef; color: #666;
    font-size: 11px;
}
.step-check { display: none; }
.step.active { background: #1a1f3c; color: #fff; }
.step.active .step-badge { background: #4f5ed4; color: #fff; }
.step.done { background: #198754; color: #fff; }
.step.done .step-badge { display: none; }
.step.done .step-check { display: inline-flex; }
@media (max-width: 576px) {
    .step-indicator { flex-direction: column; }
    .step { border-right: none; border-bottom: 2px solid #fff; justify-content: flex-start; padding: 10px 14px; }
    .step:last-child { border-bottom: none; }
}

/* Format reference */
.format-hint {
    background: #f8f9fd;
    border: 1px solid #e5e8f5;
    border-radius: 8px;
    padding: 14px 16px;
}
.format-hint-title { font-size: 12px; font-weight: 600; color: #555; margin-bottom: 8px; }
.format-example {
    background: #0f1225;
    color: #c9d1ff;
    border: none;
    border-radius: 8px;
    padding: 14px 16px;
    font-family: 'SFMono-Regular', Consolas, monospace;
    font-size: 11px;
    line-height: 1.6;
    overflow-x: auto;
}

/* Preview table */
.preview-section { display: none; }
.preview-table-wrap {
    border: 1px solid #e5e8f5;
    border-radius: 8px;
    overflow: hidden;
}
#preview-table thead th {
    background: #f8f9fd;
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: .03em;
    color: #666;
    border-top: none;
}
#preview-table tbody tr:nth-child(even) { background: #fafbff; }
#preview-table tbody tr:hover { background: #eef0ff; }
#preview-table .badge { font-size: 11px; padding: 4px 10px; border-radius: 20px; font-weight: 600; }
.preview-ok  td:first-child { border-left: 3px solid #198754; }
.preview-err td:first-child { border-left: 3px solid #dc3545; }

.error-chip {
    display: inline-block;
    background: #fee2e2; color: #991b1b;
    border-radius: 4px; padding: 2px 8px;
    font-size: 11px; margin: 1px;
}

.btn:disabled { opacity: .6; cursor: not-allowed; }
</style>
@endpush

@section('content')
<div class="content-wrapper">
    <div class="row justify-content-center">
        <div class="col-lg-10">

            <div class="import-card">
                <div class="import-header">
                    <h4><i class="fa fa-upload mr-2"></i>@lang('modules.booking.importPassengers')</h4>
                    <p>@lang('modules.booking.importPassengersHint')</p>
                </div>
                <div class="p-4">

                    {{-- Step indicator --}}
                    <div class="step-indicator" role="group" aria-label="@lang('modules.booking.importPassengers')">
                        <div class="step active" id="step-1">
                            <span class="step-badge">1</span>
                            <i class="fa fa-check step-check"></i>
                            <span>@lang('modules.booking.uploadFile')</span>
                        </div>
                        <div class="step" id="step-2">
                            <span class="step-badge">2</span>
                            <i class="fa fa-check step-check"></i>
                            <span>@lang('modules.booking.previewAndValidate')</span>
                        </div>
                        <div class="step" id="step-3">
                            <span class="step-badge">3</span>
                            <i class="fa fa-check step-check"></i>
                            <span>@lang('modules.booking.confirm')</span>
                        </div>
                    </div>

                    {{-- Step 1: Upload --}}
                    <div id="upload-section">

                        {{-- Booking selector --}}
                        <div class="form-group mb-4">
                            <label for="booking_group_id" class="font-weight-600">@lang('modules.booking.selectBookingGroup') <span class="text-danger">*</span></label>
                            <select id="booking_group_id" name="booking_group_id" class="form-control select-picker" required>
                                <option value="">-- @lang('app.select') --</option>
                                @foreach($bookingGroups as $bg)
                                    <option value="{{ $bg->id }}" @selected($selectedBookingId == $bg->id)>
                                        {{ $bg->group_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Upload zone --}}
                        <div class="upload-zone" id="upload-zone" onclick="$('#file-input').click()">
                            <div class="icon"><i class="fa fa-file-csv"></i></div>
                            <div class="font-weight-600">@lang('modules.booking.dragDropOrClick')</div>
                            <div class="hint">@lang('modules.booking.supportedFormats'): CSV, XLSX, XLS — @lang('modules.booking.maxSize'): 5MB</div>
                            <input type="file" id="file-input" accept=".csv,.xlsx,.xls" style="display:none">
                        </div>

                        {{-- Format reference --}}
                        <div class="format-hint mt-3">
                            <div class="format-hint-title"><i class="fa fa-info-circle mr-1"></i>@lang('modules.booking.expectedFormat'):</div>
                            <pre class="format-example mb-0">Group Detail
GroupNo, KH-EM2A1AN-0011
GroupName, Umrah 2024 Group A
PassportNo, First Name, Family Name, Birth Date, Gender, Mofa
LB5166012, SYED, ZAFAR HUSSAIN SHAH, 01/01/1950, Male, 0
BS8978321, AMEEN, FARIDA, 01/01/1962, Female, 0</pre>
                        </div>

                        <button class="btn btn-primary mt-3" id="btn-parse" disabled>
                            <i class="fa fa-search mr-1"></i> @lang('modules.booking.parseAndPreview')
                        </button>
                    </div>

                    {{-- Step 2: Preview --}}
                    <div class="preview-section" id="preview-section">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div>
                                <h6 class="mb-0" id="preview-title">@lang('modules.booking.previewPassengers')</h6>
                                <small class="text-muted" id="preview-summary"></small>
                            </div>
                            <button class="btn btn-sm btn-outline-secondary" id="btn-re-upload">
                                <i class="fa fa-redo"></i> @lang('app.tryAgain')
                            </button>
                        </div>

                        {{-- Errors --}}
                        <div id="parse-errors" style="display:none" class="alert alert-danger"></div>

                        <div class="table-responsive preview-table-wrap">
                            <table class="table table-sm table-bordered mb-0" id="preview-table">
                                <thead class="thead-light">
                                    <tr>
                                        <th><input type="checkbox" id="check-all"></th>
                                        <th>@lang('modules.booking.passportNo')</th>
                                        <th>@lang('app.name')</th>
                                        <th>@lang('app.gender')</th>
                                        <th>@lang('modules.booking.dob')</th>
                                        <th>@lang('modules.booking.mofa')</th>
                                        <th>@lang('app.status')</th>
                                    </tr>
                                </thead>
                                <tbody id="preview-body"></tbody>
                            </table>
                        </div>

                        <button class="btn btn-success mt-3" id="btn-commit">
                            <i class="fa fa-check mr-1"></i> @lang('modules.booking.importSelected')
                        </button>
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
'use strict';

let parsedPassengers = [];
const CSRF = '{{ csrf_token() }}';

// ── Drag & drop ────────────────────────────────────────────────────────────────
const zone = document.getElementById('upload-zone');
const fi   = document.getElementById('file-input');

zone.addEventListener('dragover',  e => { e.preventDefault(); zone.classList.add('drag-over'); });
zone.addEventListener('dragleave', () => zone.classList.remove('drag-over'));
zone.addEventListener('drop', e => {
    e.preventDefault();
    zone.classList.remove('drag-over');
    const f = e.dataTransfer.files[0];
    if (f) handleFile(f);
});

fi.addEventListener('change', function() {
    if (this.files[0]) handleFile(this.files[0]);
});

function handleFile(file) {
    zone.querySelector('.icon i').className = 'fa fa-file';
    zone.querySelector('.font-weight-600').textContent = file.name;
    zone.querySelector('.hint').textContent = (file.size / 1024).toFixed(1) + ' KB';
    document.getElementById('btn-parse').disabled = !$('#booking_group_id').val();
    fi._selectedFile = file;
}

$('#booking_group_id').on('change', function() {
    document.getElementById('btn-parse').disabled = !(fi._selectedFile && this.value);
});

// ── Parse ──────────────────────────────────────────────────────────────────────
$('#btn-parse').on('click', function() {
    const bookingId = $('#booking_group_id').val();
    if (!bookingId || !fi._selectedFile) return;

    const formData = new FormData();
    formData.append('_token', CSRF);
    formData.append('file', fi._selectedFile);
    formData.append('booking_group_id', bookingId);

    $(this).prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-1"></i> Parsing...');

    $.ajax({
        url: '{{ route("bookings.import.parse") }}',
        type: 'POST',
        data: formData,
        contentType: false,
        processData: false,
        success: function(response) {
            $('#btn-parse').prop('disabled', false).html('<i class="fa fa-search mr-1"></i> @lang("modules.booking.parseAndPreview")');
            renderPreview(response.data);
        },
        error: function(xhr) {
            $('#btn-parse').prop('disabled', false).html('<i class="fa fa-search mr-1"></i> @lang("modules.booking.parseAndPreview")');
            window.toastr.error('Parse error: ' + (xhr.responseJSON?.message || 'Unknown error'));
        }
    });
});

function renderPreview(data) {
    parsedPassengers = data.passengers || [];

    $('#upload-section').hide();
    $('#preview-section').show();
    $('#step-1').removeClass('active').addClass('done');
    $('#step-2').addClass('active');

    $('#preview-summary').text(
        data.total + ' @lang("modules.booking.passengersParsed")' +
        (data.group_name ? ' — Group: ' + data.group_name : '')
    );

    const errorsDiv = $('#parse-errors');
    if (data.errors && data.errors.length) {
        errorsDiv.show().html('<strong>Validation Errors:</strong><br>' +
            data.errors.map(e => `<span class="error-chip">${e}</span>`).join(' ')
        );
    } else {
        errorsDiv.hide();
    }

    const tbody = $('#preview-body');
    tbody.empty();

    parsedPassengers.forEach((pax, idx) => {
        const row = `
            <tr class="preview-ok">
                <td><input type="checkbox" class="pax-check" value="${idx}" checked></td>
                <td><code>${pax.passport_no}</code></td>
                <td>${pax.first_name} ${pax.family_name}</td>
                <td>${pax.gender}</td>
                <td>${pax.birth_date || '—'}</td>
                <td>${pax.mofa_status}</td>
                <td><span class="badge badge-success">@lang("app.ready")</span></td>
            </tr>`;
        tbody.append(row);
    });
}

// Select all
$('#check-all').on('change', function() {
    $('.pax-check').prop('checked', this.checked);
});

// ── Commit ─────────────────────────────────────────────────────────────────────
$('#btn-commit').on('click', function() {
    const selected = [];
    $('.pax-check:checked').each(function() {
        selected.push(parsedPassengers[parseInt($(this).val())]);
    });

    if (!selected.length) {
        window.toastr.warning('@lang("modules.booking.noneSelected")');
        return;
    }

    const bookingId = $('#booking_group_id').val();
    $(this).prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-1"></i> Importing...');

    $.easyAjax({
        url: '{{ route("bookings.import.commit") }}',
        type: 'POST',
        data: {
            _token: CSRF,
            booking_group_id: bookingId,
            passengers: selected,
        },
        success: function(response) {
            if (response.status === 'success') {
                $('#step-2').removeClass('active').addClass('done');
                $('#step-3').addClass('active');
                window.toastr.success(response.message);
                setTimeout(() => window.location.href = response.redirectUrl, 1200);
            }
        }
    });
});

$('#btn-re-upload').on('click', function() {
    $('#preview-section').hide();
    $('#upload-section').show();
    $('#step-1').addClass('active').removeClass('done');
    $('#step-2').removeClass('active');
    fi._selectedFile = null;
    fi.value = '';
});
</script>
@endpush