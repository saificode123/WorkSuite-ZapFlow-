@extends('layouts.app')

@push('datatable-styles')
    @include('sections.datatable_css')
@endpush

@section('content')
<div class="content-wrapper">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">@lang('app.menu.hotelRooms')</h4>
        <div>
            <select id="hotel-filter" class="form-control form-control-sm" style="width:250px;display:inline-block">
                <option value="">@lang('app.allHotels')</option>
                @foreach($hotels as $hotel)
                    <option value="{{ $hotel->id }}" {{ request('hotel_id') == $hotel->id ? 'selected' : '' }}>{{ $hotel->name }}</option>
                @endforeach
            </select>
            <x-forms.link-primary :link="route('hotel-rooms.create')" class="ml-2 openRightModal" icon="plus">
                @lang('app.addRoom')
            </x-forms.link-primary>
        </div>
    </div>
    <div class="d-flex flex-column w-tables rounded mt-3 bg-white w-100 table-responsive">
        <table class="table table-hover border-0 w-100" id="hotel-rooms-table">
            <thead>
                <tr>
                    <th>@lang('app.hotel')</th>
                    <th>@lang('app.roomNumber')</th>
                    <th>@lang('app.roomType')</th>
                    <th>@lang('app.floor')</th>
                    <th>@lang('app.capacity')</th>
                    <th>@lang('app.genderRestriction')</th>
                    <th>@lang('app.status')</th>
                    <th>@lang('app.action')</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rooms as $room)
                <tr>
                    <td>{{ $room->hotel?->name ?? '—' }}</td>
                    <td><strong>{{ $room->room_number ?? '—' }}</strong></td>
                    <td><span class="badge badge-info">{{ ucfirst($room->room_type) }}</span></td>
                    <td>{{ $room->floor ?? '—' }}</td>
                    <td>{{ $room->capacity }} @lang('app.persons')</td>
                    <td>{{ $room->gender_restriction ?? '—' }}</td>
                    <td><span class="badge badge-{{ $room->is_available ? 'success' : 'secondary' }}">{{ $room->is_available ? __('app.available') : __('app.unavailable') }}</span></td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary openRightModal"
                                data-href="{{ route('hotel-rooms.edit', $room->id) }}">
                            <i class="fa fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger delete-room" data-room-id="{{ $room->id }}">
                            <i class="fa fa-trash"></i>
                        </button>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center py-4 text-muted">@lang('messages.noRecordFound')</td></tr>
                @endforelse
            </tbody>
        </table>
        {{ $rooms->links() }}
    </div>
</div>
@endsection

@push('scripts')
<script>
$('#hotel-filter').change(function() {
    const hotelId = $(this).val();
    window.location.href = "{{ route('hotel-rooms.index') }}?hotel_id=" + hotelId;
});

$('body').on('click', '.delete-room', function() {
    const id = $(this).data('room-id');
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
                url: "{{ route('hotel-rooms.destroy', ':id') }}".replace(':id', id),
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
