@extends('layouts.app')

@push('datatable-styles')
    @include('sections.datatable_css')
@endpush

@section('content')
<div class="content-wrapper">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">@lang('app.menu.cashReceipts')</h4>
        @if (in_array(user()->permission('add_cash_receipt'), ['all', 'added']))
            <x-forms.link-primary :link="route('cash-receipts.create')" class="openRightModal" icon="plus">
                @lang('app.addCashReceipt')
            </x-forms.link-primary>
        @endif
    </div>
    <div class="d-flex flex-column w-tables rounded mt-3 bg-white w-100 table-responsive">
        <table class="table table-hover border-0 w-100">
            <thead>
                <tr>
                    <th>@lang('app.id')</th>
                    <th>@lang('app.date')</th>
                    <th>@lang('app.receivedFrom')</th>
                    <th>@lang('app.account')</th>
                    <th class="text-right">@lang('app.amount')</th>
                    <th>@lang('app.reference')</th>
                    <th>@lang('app.action')</th>
                </tr>
            </thead>
            <tbody>
                @forelse($receipts as $receipt)
                <tr>
                    <td>#{{ $receipt->id }}</td>
                    <td>{{ $receipt->date ? \Carbon\Carbon::parse($receipt->date)->format('d M Y') : '—' }}</td>
                    <td>{{ $receipt->received_from }}</td>
                    <td>{{ $receipt->account?->name ?? '—' }}</td>
                    <td class="text-right font-weight-bold text-success">{{ number_format($receipt->amount, 2) }}</td>
                    <td>{{ $receipt->reference_no ?? '—' }}</td>
                    <td>
                        <button class="btn btn-sm btn-outline-danger delete-receipt" data-receipt-id="{{ $receipt->id }}">
                            <i class="fa fa-trash"></i>
                        </button>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center py-4 text-muted">@lang('messages.noRecordFound')</td></tr>
                @endforelse
            </tbody>
        </table>
        {{ $receipts->links() }}
    </div>
</div>
@endsection

@push('scripts')
<script>
$('body').on('click', '.delete-receipt', function() {
    const id = $(this).data('receipt-id');
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
                    if (response.status == "success") window.location.reload();
                }
            });
        }
    });
});
</script>
@endpush
