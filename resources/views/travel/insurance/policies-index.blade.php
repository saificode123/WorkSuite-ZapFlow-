@extends('layouts.app')

@push('datatable-styles')
    @include('sections.datatable_css')
@endpush

@php $addPermission = user()->permission('add_insurance_policy'); @endphp

@section('content')
<div class="content-wrapper">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">@lang('app.menu.insurancePolicies')</h4>
        @if (in_array($addPermission, ['all', 'added']))
            <button class="btn btn-primary openRightModal" data-href="{{ route('insurance-policies.create') }}">
                <i class="fa fa-plus"></i> @lang('app.addPolicy')
            </button>
        @endif
    </div>
    <div class="d-flex flex-column w-tables rounded mt-3 bg-white w-100 table-responsive">
        <table class="table table-hover border-0 w-100">
            <thead>
                <tr>
                    <th>@lang('app.name')</th>
                    <th>@lang('app.provider')</th>
                    <th>@lang('app.coverageType')</th>
                    <th class="text-right">@lang('app.premiumPerPax')</th>
                    <th>@lang('app.validity')</th>
                    <th>@lang('app.status')</th>
                    <th>@lang('app.action')</th>
                </tr>
            </thead>
            <tbody>
                @forelse($policies as $policy)
                <tr>
                    <td>{{ $policy->name }}</td>
                    <td>{{ $policy->provider ?? '—' }}</td>
                    <td>{{ $policy->coverage_type ?? '—' }}</td>
                    <td class="text-right">{{ number_format($policy->premium_pax ?? 0, 2) }}</td>
                    <td>
                        @if($policy->valid_from && $policy->valid_to)
                            {{ \Carbon\Carbon::parse($policy->valid_from)->format('d M Y') }} — {{ \Carbon\Carbon::parse($policy->valid_to)->format('d M Y') }}
                        @else
                            @lang('app.openEnded')
                        @endif
                    </td>
                    <td><span class="badge badge-{{ $policy->is_active ? 'success' : 'secondary' }}">{{ $policy->is_active ? __('app.active') : __('app.inactive') }}</span></td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary openRightModal"
                                data-href="{{ route('insurance-policies.edit', $policy->id) }}">
                            <i class="fa fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger delete-policy" data-policy-id="{{ $policy->id }}">
                            <i class="fa fa-trash"></i>
                        </button>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center py-4 text-muted">@lang('messages.noRecordFound')</td></tr>
                @endforelse
            </tbody>
        </table>
        {{ $policies->links() }}
    </div>
</div>
@endsection

@push('scripts')
<script>
$('body').on('click', '.delete-policy', function() {
    const id = $(this).data('policy-id');
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
                    if (response.status == "success") window.location.reload();
                }
            });
        }
    });
});
</script>
@endpush
