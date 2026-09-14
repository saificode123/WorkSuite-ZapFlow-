@extends('layouts.app')

@push('datatable-styles')
    @include('sections.datatable_css')
@endpush

@section('content')
<div class="content-wrapper">

    {{-- Page header --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="d-flex align-items-center">
            <div class="icon-circle bg-light-success text-success mr-3">
                <i class="fa fa-hand-holding-usd" aria-hidden="true"></i>
            </div>
            <div>
                <h4 class="mb-0 font-weight-normal">@lang('modules.travelPayments.receivePayments')</h4>
            </div>
        </div>
        <div class="d-flex align-items-center">
            <div id="table-actions" class="d-flex"></div>
            @if (in_array(user()->permission('add_travel_payment'), ['all', 'added']))
                <x-forms.link-primary :link="route('travel-payments.receive.create')" class="openRightModal ml-2" icon="plus">
                    @lang('app.receivePayment')
                </x-forms.link-primary>
            @endif
        </div>
    </div>

    {{-- Table --}}
    <div class="d-flex flex-column w-tables rounded mt-3 bg-white w-100 table-responsive shadow-sm">
        {!! $dataTable->table(['class' => 'table table-hover border-0 w-100 align-middle']) !!}
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
        flex-shrink: 0;
    }
    .bg-light-success { background-color: rgba(40, 167, 69, 0.1); }
    .badge-pill { padding: 5px 10px; }
</style>
@endsection

@push('scripts')
    @include('sections.datatable_js')
    {!! $dataTable->scripts() !!}
    <script>
        $('body').on('click', '.cancel-payment-btn', function() {
            const id = $(this).data('payment-id');
            Swal.fire({
                title: "@lang('messages.sweetAlertTitle')",
                text: "@lang('messages.confirmCancelPayment')",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: "@lang('app.yesCancel')",
                cancelButtonText: "@lang('app.cancel')",
                customClass: { confirmButton: 'btn btn-danger mr-3', cancelButton: 'btn btn-secondary' },
                buttonsStyling: false,
            }).then(result => {
                if (result.isConfirmed) {
                    $.easyAjax({
                        url: "{{ route('travel-payments.cancel', ':id') }}".replace(':id', id),
                        type: 'POST',
                        data: { _token: '{{ csrf_token() }}' },
                        success: function(response) {
                            if (response.status === 'success') window.LaravelDataTables["travel-payments-table"].draw();
                        }
                    });
                }
            });
        });
    </script>
@endpush
