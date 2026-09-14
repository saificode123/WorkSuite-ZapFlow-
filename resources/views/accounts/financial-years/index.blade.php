@extends('layouts.app')

@push('datatable-styles')
    @include('sections.datatable_css')
@endpush

@section('content')
    <div class="content-wrapper">
        <div class="d-flex justify-content-between">
            <div id="table-actions" class="align-items-center mb-2 mb-lg-0 mb-md-0">
                @if (user()->permission('add_financial_year') == 'all')
                    <x-forms.link-primary :link="route('financial-years.create')" class="mr-3 float-left mb-2 mb-lg-0 mb-md-0" icon="plus">
                        @lang('modules.accounts.addFinancialYear')
                    </x-forms.link-primary>
                @endif
            </div>
        </div>
        <div class="d-flex flex-column w-tables rounded mt-3 bg-white">
            {!! $dataTable->table(['class' => 'table table-hover border-0 w-100']) !!}
        </div>
    </div>
@endsection

@push('scripts')
    @include('sections.datatable_js')
    <script>
        $('#financial-years-table').on('click', '.delete-table-row', function() {
            var id = $(this).data('row-id');
            Swal.fire({
                title: "@lang('messages.sweetAlertTitle')",
                text: "@lang('messages.recoverRecord')",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: "@lang('messages.confirmDelete')"
            }).then((result) => {
                if (result.isConfirmed) {
                    var url = "{{ route('financial-years.destroy', ':id') }}";
                    url = url.replace(':id', id);
                    var token = "{{ csrf_token() }}";
                    $.easyAjax({
                        type: 'POST',
                        url: url,
                        data: { '_token': token, '_method': 'DELETE' },
                        success: function(response) {
                            if (response.status == 'success') {
                                window.LaravelDataTables['financial-years-table'].draw(false);
                            }
                        }
                    });
                }
            });
        });

        $('#financial-years-table').on('click', '.close-financial-year', function() {
            var id = $(this).data('row-id');
            Swal.fire({
                title: "Close Financial Year?",
                text: "Closing this financial year will calculate net P&L and roll balances into next year. Once closed, no new journal vouchers or modifications can be posted in this period.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: "Yes, Close Year!"
            }).then((result) => {
                if (result.isConfirmed) {
                    var url = "{{ route('financial-years.close', ':id') }}";
                    url = url.replace(':id', id);
                    var token = "{{ csrf_token() }}";
                    $.easyAjax({
                        type: 'POST',
                        url: url,
                        data: { '_token': token },
                        success: function(response) {
                            if (response.status == 'success') {
                                window.LaravelDataTables['financial-years-table'].draw(false);
                            }
                        }
                    });
                }
            });
        });
    </script>
@endpush

