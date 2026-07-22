@extends('layouts.app')

@push('datatable-styles')
    @include('sections.datatable_css')
@endpush

@section('content')
<div class="content-wrapper">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">@lang('app.menu.insuranceSales')</h4>
        <button class="btn btn-primary openRightModal" data-href="{{ route('insurance-sales.create') }}">
            <i class="fa fa-plus"></i> @lang('app.addSale')
        </button>
    </div>
    <div class="d-flex flex-column w-tables rounded mt-3 bg-white w-100 table-responsive">
        <table class="table table-hover border-0 w-100">
            <thead>
                <tr>
                    <th>@lang('app.passenger')</th>
                    <th>@lang('app.policy')</th>
                    <th>@lang('app.bookingGroup')</th>
                    <th class="text-right">@lang('app.amount')</th>
                    <th>@lang('app.certificateNo')</th>
                    <th>@lang('app.status')</th>
                    <th>@lang('app.date')</th>
                    <th>@lang('app.action')</th>
                </tr>
            </thead>
            <tbody>
                @forelse($sales as $sale)
                <tr>
                    <td>{{ $sale->passenger?->full_name ?? '—' }}</td>
                    <td>{{ $sale->policy?->name ?? '—' }}</td>
                    <td>{{ $sale->bookingGroup?->group_name ?? '—' }}</td>
                    <td class="text-right">{{ number_format($sale->amount ?? 0, 2) }}</td>
                    <td>{{ $sale->certificate_number ?? '—' }}</td>
                    <td><span class="badge badge-{{ $sale->status === 'active' ? 'success' : 'secondary' }}">{{ ucfirst($sale->status) }}</span></td>
                    <td>{{ $sale->created_at ? $sale->created_at->format('d M Y') : '—' }}</td>
                    <td>
                        <button class="btn btn-sm btn-outline-danger delete-sale" data-sale-id="{{ $sale->id }}">
                            <i class="fa fa-trash"></i>
                        </button>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center py-4 text-muted">@lang('messages.noRecordFound')</td></tr>
                @endforelse
            </tbody>
        </table>
        {{ $sales->links() }}
    </div>
</div>
@endsection

@push('scripts')
<script>
$('body').on('click', '.delete-sale', function() {
    const id = $(this).data('sale-id');
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
                url: "{{ route('insurance-sales.destroy', ':id') }}".replace(':id', id),
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
