@extends('layouts.app')

@push('datatable-styles')
    @include('sections.datatable_css')
@endpush

@section('content')
<div class="content-wrapper">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">@lang('modules.travelPayments.receivePayments')</h4>
        @if (in_array(user()->permission('add_travel_payment'), ['all', 'added']))
            <x-forms.link-primary :link="route('travel-payments.receive.create')" class="openRightModal" icon="plus">
                @lang('app.receivePayment')
            </x-forms.link-primary>
        @endif
    </div>

    <div class="d-flex flex-column w-tables rounded mt-3 bg-white w-100 table-responsive">
        <table class="table table-hover border-0 w-100" id="receive-payments-table">
            <thead>
                <tr>
                    <th>@lang('app.id')</th>
                    <th>@lang('app.date')</th>
                    <th>@lang('app.receivedFrom')</th>
                    <th>@lang('app.reference')</th>
                    <th>@lang('app.debitAccount')</th>
                    <th class="text-right">@lang('app.amount')</th>
                    <th>@lang('app.status')</th>
                    <th>@lang('app.action')</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payments as $payment)
                <tr>
                    <td>#{{ $payment->id }}</td>
                    <td>{{ $payment->payment_date ? \Carbon\Carbon::parse($payment->payment_date)->format('d M Y') : '—' }}</td>
                    <td>{{ $payment->partyUser?->name ?? '—' }}</td>
                    <td>{{ $payment->reference_no ?? '—' }}</td>
                    <td>{{ $payment->debitAccount?->name ?? '—' }}</td>
                    <td class="text-right font-weight-bold text-success">{{ number_format($payment->amount, 2) }}</td>
                    <td><span class="badge badge-{{ $payment->status === 'posted' ? 'success' : 'secondary' }}">{{ ucfirst($payment->status) }}</span></td>
                    <td>
                        @if($payment->status === 'posted')
                        <button class="btn btn-sm btn-outline-danger cancel-payment" data-payment-id="{{ $payment->id }}">
                            <i class="fa fa-ban"></i> @lang('app.cancel')
                        </button>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center py-4 text-muted">@lang('messages.noRecordFound')</td></tr>
                @endforelse
            </tbody>
        </table>
        {{ $payments->links() }}
    </div>
</div>
@endsection

@push('scripts')
<script>
$('body').on('click', '.cancel-payment', function() {
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
                    if (response.status === 'success') window.location.reload();
                }
            });
        }
    });
});
</script>
@endpush
