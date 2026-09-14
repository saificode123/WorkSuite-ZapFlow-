@extends('layouts.app')

@push('datatable-styles')
    @include('sections.datatable_css')
@endpush

@section('content')
<div class="content-wrapper">
    <div class="add-client bg-white rounded shadow-sm">
        <div class="d-block d-lg-flex d-md-flex justify-content-between align-items-center border-bottom-grey p-20">
            <h4 class="mb-0 f-21 font-weight-normal">
                <i class="fa fa-receipt mr-2 text-primary"></i>@lang('app.menu.cashReceipts')
            </h4>
            <div class="d-flex align-items-center">
                <div id="table-actions" class="d-flex"></div>
                @if (in_array(user()->permission('add_cash_receipt'), ['all', 'added']))
                    <x-forms.link-primary :link="route('cash-receipts.create')" class="openRightModal mt-2 mt-lg-0 mt-md-0 ml-2" icon="plus">
                        @lang('app.addCashReceipt')
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
                        url: "{{ route('cash-receipts.destroy', ':id') }}".replace(':id', id),
                        blockUI: true,
                        data: { '_token': "{{ csrf_token() }}", '_method': 'DELETE' },
                        success: function(response) {
                            if (response.status == "success") window.LaravelDataTables["cash-receipts-table"].draw();
                        }
                    });
                }
            });
        });
    </script>
@endpush
