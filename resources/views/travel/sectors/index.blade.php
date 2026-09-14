@extends('layouts.app')

@push('datatable-styles')
    @include('sections.datatable_css')
@endpush

@php
$addPermission = user()->permission('add_sector');
@endphp

@section('content')
    <div class="content-wrapper sector-index">

        <div class="d-flex justify-content-between align-items-center flex-wrap mb-3">
            <div>
                <h4 class="mb-0 sector-page-title">@lang('app.sector')</h4>
                <span class="sector-page-sub">@lang('app.manageSectorsBelow')</span>
            </div>
        </div>

        <div class="d-block d-lg-flex d-md-flex justify-content-between">
            <div id="table-actions" class="flex-grow-1 align-items-center mb-2 mb-lg-0 mb-md-0">
                @if (in_array($addPermission, ['all', 'added']))
                    <x-forms.link-primary :link="route('sectors.create')" class="mr-3 float-left openRightModal sector-add-btn" icon="plus">
                        @lang('app.add') @lang('app.sector')
                    </x-forms.link-primary>
                @endif
            </div>
        </div>

        <div class="d-flex flex-column w-tables rounded mt-3 bg-white w-100 table-responsive sector-table-card">
            {!! $dataTable->table(['class' => 'table table-hover border-0 w-100 sector-table']) !!}
        </div>
    </div>
@endsection

@push('css')
<style>
.sector-index {
    --sc-border:   #e7e9f2;
    --sc-primary:  #4f6ef7;
    --sc-primary-soft: #eef1ff;
    --sc-muted:    #7a8194;
    --sc-text:     #1f2430;
    --sc-danger:   #e2493d;
    --sc-danger-soft: #fdece9;
}

.sector-page-title {
    font-weight: 700;
    letter-spacing: -.01em;
    color: var(--sc-text);
}

.sector-page-sub {
    display: block;
    font-size: 12.5px;
    color: var(--sc-muted);
    margin-top: 2px;
}

.sector-add-btn {
    border-radius: 8px;
    font-weight: 600;
    box-shadow: 0 4px 10px -4px rgba(79,110,247,.5);
    transition: transform .12s ease, box-shadow .12s ease;
}
.sector-add-btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 6px 14px -4px rgba(79,110,247,.55);
}

.sector-table-card {
    border: 1px solid var(--sc-border);
    box-shadow: 0 1px 2px rgba(20,24,40,.04), 0 8px 20px -10px rgba(20,24,40,.10);
    padding: 6px 12px;
}

.sector-table thead th {
    font-size: 11px;
    font-weight: 700;
    letter-spacing: .05em;
    text-transform: uppercase;
    color: var(--sc-muted);
    border-bottom: 1px solid var(--sc-border);
    white-space: nowrap;
}

.sector-table tbody tr {
    transition: background .12s ease;
}
.sector-table tbody tr:hover {
    background: var(--sc-primary-soft);
}
.sector-table tbody td {
    vertical-align: middle;
    color: var(--sc-text);
    border-top: 1px solid var(--sc-border);
}

.sector-table .delete-table-row {
    color: var(--sc-danger);
    transition: background .15s ease, opacity .15s ease;
    border-radius: 6px;
}
.sector-table .delete-table-row:hover {
    background: var(--sc-danger-soft);
}

@media (prefers-reduced-motion: reduce) {
    .sector-add-btn, .sector-table tbody tr, .sector-table .delete-table-row { transition: none; }
}
</style>
@endpush

@push('scripts')
    @include('sections.datatable_js')
    <script>
        const showTable = () => window.LaravelDataTables["sectors-table"].draw(true);
        $('body').on('click', '.delete-table-row', function() {
            var id = $(this).data('sector-id');
            Swal.fire({
                title: "@lang('messages.sweetAlertTitle')",
                text: "@lang('messages.recoverRecord')",
                icon: 'warning',
                showCancelButton: true,
                focusConfirm: false,
                confirmButtonText: "@lang('messages.confirmDelete')",
                cancelButtonText: "@lang('app.cancel')",
                customClass: { confirmButton: 'btn btn-primary mr-3', cancelButton: 'btn btn-secondary' },
                showClass: { popup: 'swal2-noanimation', backdrop: 'swal2-noanimation' },
                buttonsStyling: false
            }).then((result) => {
                if (result.isConfirmed) {
                    $.easyAjax({
                        type: 'POST', url: "{{ route('sectors.destroy', ':id') }}".replace(':id', id),
                        blockUI: true, data: { '_token': "{{ csrf_token() }}", '_method': 'DELETE' },
                        success: function(response) { if (response.status == "success") showTable(); }
                    });
                }
            });
        });
    </script>
@endpush