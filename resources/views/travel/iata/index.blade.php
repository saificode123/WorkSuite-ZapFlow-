@extends('layouts.app')

@push('datatable-styles')
    @include('sections.datatable_css')
@endpush

@php
$addPermission = user()->permission('add_iata');
@endphp

@section('content')
    <div class="content-wrapper">
        <div class="d-block d-lg-flex d-md-flex justify-content-between align-items-center mb-2 ct-page-header">
            <div class="d-flex align-items-center mb-2 mb-lg-0 mb-md-0">
                <div class="ct-header-icon mr-3">
                    <i class="fa fa-plane-departure"></i>
                </div>
                <h4 class="mb-0 f-21 font-weight-normal">@lang('app.iata')</h4>
            </div>

            <div id="table-actions" class="flex-grow-1 align-items-center mb-2 mb-lg-0 mb-md-0">
                @if (in_array($addPermission, ['all', 'added']))
                    <x-forms.link-primary :link="route('iata.create')" class="mr-3 float-left openRightModal" icon="plus">
                        @lang('app.add') @lang('app.iata')
                    </x-forms.link-primary>
                @endif
            </div>
        </div>

        <div class="d-flex flex-column w-tables rounded mt-3 bg-white w-100 table-responsive shadow-sm ct-table-card">
            {!! $dataTable->table(['class' => 'table table-hover border-0 w-100']) !!}
        </div>
    </div>

    <style>
        .ct-page-header { animation: ctFadeIn .2s ease-out; }
        @keyframes ctFadeIn {
            from { opacity: 0; transform: translateY(4px); }
            to   { opacity: 1; transform: translateY(0); }
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
        .ct-table-card {
            transition: box-shadow .15s ease;
        }
        .ct-table-card table.table-hover tbody tr:hover {
            background-color: rgba(0, 123, 255, .03);
        }
    </style>
@endsection

@push('scripts')
    @include('sections.datatable_js')
    <script>
        const showTable = () => window.LaravelDataTables["iata-table"].draw(true);
        $('body').on('click', '.delete-table-row', function() {
            var id = $(this).data('iata-id');
            Swal.fire({
                title: "@lang('messages.sweetAlertTitle')",
                text: "@lang('messages.recoverRecord')",
                icon: 'warning',
                showCancelButton: true,
                focusConfirm: false,
                confirmButtonText: "@lang('messages.confirmDelete')",
                cancelButtonText: "@lang('app.cancel')",
                customClass: { confirmButton: 'btn btn-primary mr-3', cancelButton: 'btn btn-secondary', popup: 'ct-swal-popup' },
                showClass: { popup: 'swal2-noanimation', backdrop: 'swal2-noanimation' },
                buttonsStyling: false
            }).then((result) => {
                if (result.isConfirmed) {
                    $.easyAjax({
                        type: 'POST', url: "{{ route('iata.destroy', ':id') }}".replace(':id', id),
                        blockUI: true, data: { '_token': "{{ csrf_token() }}", '_method': 'DELETE' },
                        success: function(response) { if (response.status == "success") showTable(); }
                    });
                }
            });
        });
    </script>
@endpush