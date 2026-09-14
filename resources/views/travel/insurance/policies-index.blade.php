@extends('layouts.app')

@push('datatable-styles')
    @include('sections.datatable_css')
@endpush

@php $addPermission = user()->permission('add_insurance_policy'); @endphp

@section('content')
<div class="content-wrapper">
    <div class="d-block d-lg-flex d-md-flex justify-content-between align-items-center mb-3 ct-page-header">
        <div class="d-flex align-items-center mb-2 mb-lg-0 mb-md-0">
            <div class="ct-header-icon mr-3">
                <i class="fa fa-shield-alt"></i>
            </div>
            <h4 class="mb-0 f-21 font-weight-normal">@lang('app.menu.insurancePolicies')</h4>
        </div>
        <div class="d-flex align-items-center">
            <div id="table-actions" class="d-flex"></div>
            @if (in_array($addPermission, ['all', 'added']))
                <button class="btn btn-primary ml-2 openRightModal" data-href="{{ route('insurance-policies.create') }}">
                    <i class="fa fa-plus"></i> @lang('app.addPolicy')
                </button>
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
    .ct-table-card table.table-hover tbody tr:hover {
        background-color: rgba(0, 123, 255, .03);
    }
</style>
@endsection

@push('scripts')
    @include('sections.datatable_js')
    {!! $dataTable->scripts() !!}
    <script>
        $('body').on('click', '.delete-table-row', function() {
            const id = $(this).data('row-id');
            Swal.fire({
                title: "@lang('messages.sweetAlertTitle')",
                text: "@lang('messages.recoverRecord')",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: "@lang('messages.confirmDelete')",
                cancelButtonText: "@lang('app.cancel')",
                customClass: { confirmButton: 'btn btn-primary mr-3', cancelButton: 'btn btn-secondary' },
                buttonsStyling: false
            }).then(result => {
                if (result.isConfirmed) {
                    $.easyAjax({
                        type: 'POST',
                        url: "{{ route('insurance-policies.destroy', ':id') }}".replace(':id', id),
                        blockUI: true,
                        data: { '_token': "{{ csrf_token() }}", '_method': 'DELETE' },
                        success: function(response) {
                            if (response.status == "success") window.LaravelDataTables["insurance-policies-table"].draw();
                        }
                    });
                }
            });
        });
    </script>
@endpush
