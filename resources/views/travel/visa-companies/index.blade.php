@extends('layouts.app')

@push('datatable-styles')
    @include('sections.datatable_css')
@endpush

@php
$addPermission = user()->permission('add_visa_company');
@endphp

@section('content')
    <div class="content-wrapper">
        <div class="add-client bg-white rounded shadow-sm">
            <div class="d-block d-lg-flex d-md-flex justify-content-between align-items-center border-bottom-grey p-20">
                <h4 class="mb-0 f-21 font-weight-normal">
                    <i class="fa fa-passport mr-2 text-primary"></i>Visa Companies
                </h4>
                <div id="table-actions" class="mt-2 mt-lg-0 mt-md-0">
                    @if (in_array($addPermission, ['all', 'added']))
                        <x-forms.link-primary :link="route('visa-companies.create')" class="mr-3 float-left openRightModal" icon="plus">
                            @lang('app.add') @lang('app.visaCompany')
                        </x-forms.link-primary>
                    @endif
                </div>
            </div>
            <div class="d-flex flex-column w-tables w-100 table-responsive p-20">
                {!! $dataTable->table(['class' => 'table table-hover border-0 w-100']) !!}
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    @include('sections.datatable_js')
    <script>
        const showTable = () => window.LaravelDataTables["visa-companies-table"].draw(true);
        $('body').on('click', '.delete-table-row', function() {
            var id = $(this).data('visaCompany-id');
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
                        type: 'POST', url: "{{ route('visa-companies.destroy', ':id') }}".replace(':id', id),
                        blockUI: true, data: { '_token': "{{ csrf_token() }}", '_method': 'DELETE' },
                        success: function(response) { if (response.status == "success") showTable(); }
                    });
                }
            });
        });
    </script>
@endpush