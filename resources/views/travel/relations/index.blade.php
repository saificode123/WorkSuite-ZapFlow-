@extends('layouts.app')

@push('datatable-styles')
    @include('sections.datatable_css')
@endpush

@php
    $addPermission = user()->permission('add_relation');
@endphp

@section('content')
    <div class="content-wrapper">

        {{-- Page header --}}
        <div class="d-flex align-items-center mb-3">
            <div class="icon-circle bg-light-primary text-primary mr-3">
                <i class="fa fa-sitemap" aria-hidden="true"></i>
            </div>
            <div>
                <h4 class="mb-0 font-weight-normal">@lang('app.relation')</h4>
                <small class="text-muted">@lang('modules.relation.listDescription', ['default' => 'Manage relation types used across the system.'])</small>
            </div>
        </div>

        {{-- Table actions row (id/classes unchanged — required by datatable JS) --}}
        <div class="d-block d-lg-flex d-md-flex justify-content-between">
            <div id="table-actions" class="flex-grow-1 align-items-center mb-2 mb-lg-0 mb-md-0">
                @if (in_array($addPermission, ['all', 'added']))
                    <x-forms.link-primary :link="route('relations.create')" class="mr-3 float-left openRightModal" icon="plus">
                        @lang('app.add') @lang('app.relation')
                    </x-forms.link-primary>
                @endif
            </div>
        </div>

        {{-- Table --}}
        <div class="d-flex flex-column w-tables rounded mt-3 bg-white w-100 table-responsive shadow-sm">
            {!! $dataTable->table(['class' => 'table table-hover border-0 w-100']) !!}
        </div>
    </div>

    <style>
        .icon-circle {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
        }
        .bg-light-primary { background-color: rgba(13, 110, 253, 0.1); }
    </style>
@endsection

@push('scripts')
    @include('sections.datatable_js')
    <script>
        // Redraws the relations DataTable in place (unchanged)
        const showTable = () => window.LaravelDataTables["relations-table"].draw(true);

        // Delegated delete handler for each row's delete button (unchanged)
        $('body').on('click', '.delete-table-row', function() {
            var id = $(this).data('relation-id');

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
                        type: 'POST',
                        url: "{{ route('relations.destroy', ':id') }}".replace(':id', id),
                        blockUI: true,
                        data: { '_token': "{{ csrf_token() }}", '_method': 'DELETE' },
                        success: function(response) {
                            if (response.status == "success") showTable();
                        }
                    });
                }
            });
        });
    </script>
@endpush