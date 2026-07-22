@extends('layouts.app')

@push('datatable-styles')
    @include('sections.datatable_css')
@endpush

@section('content')
    <div class="content-wrapper">
        <div class="d-flex justify-content-between">
            <div id="table-actions" class="align-items-center mb-2 mb-lg-0 mb-md-0">
                @if (user()->permission('add_exchange_rate') == 'all')
                    <x-forms.link-primary :link="route('exchange-rates.create')" class="mr-3 float-left mb-2 mb-lg-0 mb-md-0" icon="plus">
                        @lang('modules.accounts.addExchangeRate')
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
        $('#exchange-rates-table').on('click', '.delete-table-row', function() {
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
                    var url = "{{ route('exchange-rates.destroy', ':id') }}";
                    url = url.replace(':id', id);
                    var token = "{{ csrf_token() }}";
                    $.easyAjax({
                        type: 'POST',
                        url: url,
                        data: { '_token': token, '_method': 'DELETE' },
                        success: function(response) {
                            if (response.status == 'success') {
                                window.LaravelDataTables['exchange-rates-table'].draw(false);
                            }
                        }
                    });
                }
            });
        });
    </script>
@endpush
