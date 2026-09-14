<div class="modal-header">
    <div class="d-flex align-items-center">
        <div class="ct-header-icon mr-3">
            <i class="fa fa-bed"></i>
        </div>
        <h5 class="modal-title mb-0">@lang('app.editRoom')</h5>
    </div>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
</div>
<x-form id="roomEditForm" method="POST" class="ajax-form">
    <div class="modal-body">
        <div class="row">
            <div class="col-md-6 mb-3 mb-md-0">
                <x-forms.select fieldId="hotel_id" :fieldLabel="__('app.hotel')" fieldName="hotel_id" fieldRequired="true" search="true">
                    @foreach($hotels ?? \App\Models\Hotel::orderBy('name')->get() as $hotel)
                        <option value="{{ $hotel->id }}" {{ ($room->hotel_id ?? $hotel->id) == $hotel->id ? 'selected' : '' }}>{{ $hotel->name }}</option>
                    @endforeach
                </x-forms.select>
            </div>
            <div class="col-md-6">
                <x-forms.text fieldId="room_number" :fieldLabel="__('app.roomNumber')" fieldName="room_number" :fieldValue="$room->room_number ?? ''" />
            </div>

            <div class="col-md-4 mt-3">
                <x-forms.select fieldId="room_type" :fieldLabel="__('app.roomType')" fieldName="room_type" fieldRequired="true">
                    <option value="single" {{ ($room->room_type ?? '') == 'single' ? 'selected' : '' }}>@lang('app.single')</option>
                    <option value="double" {{ ($room->room_type ?? '') == 'double' ? 'selected' : '' }}>@lang('app.double')</option>
                    <option value="triple" {{ ($room->room_type ?? '') == 'triple' ? 'selected' : '' }}>@lang('app.triple')</option>
                    <option value="quad" {{ ($room->room_type ?? '') == 'quad' ? 'selected' : '' }}>@lang('app.quad')</option>
                    <option value="quint" {{ ($room->room_type ?? '') == 'quint' ? 'selected' : '' }}>@lang('app.quint')</option>
                </x-forms.select>
            </div>
            <div class="col-md-4 mt-3">
                <x-forms.number fieldId="capacity" :fieldLabel="__('app.capacity')" fieldName="capacity" :fieldValue="$room->capacity ?? 2" fieldRequired="true" />
                <small id="capacity-hint" class="text-muted f-11 d-block mt-1"></small>
            </div>
            <div class="col-md-4 mt-3">
                <x-forms.text fieldId="floor" :fieldLabel="__('app.floor')" fieldName="floor" :fieldValue="$room->floor ?? ''" />
            </div>

            <div class="col-md-6 mt-3">
                <x-forms.select fieldId="gender_restriction" :fieldLabel="__('app.genderRestriction')" fieldName="gender_restriction">
                    <option value="">@lang('app.none')</option>
                    <option value="male" {{ ($room->gender_restriction ?? '') == 'male' ? 'selected' : '' }}>@lang('app.male')</option>
                    <option value="female" {{ ($room->gender_restriction ?? '') == 'female' ? 'selected' : '' }}>@lang('app.female')</option>
                </x-forms.select>
            </div>
            <div class="col-md-6 mt-3 d-flex align-items-center">
                <x-forms.checkbox fieldId="is_available" :fieldLabel="__('app.available')" fieldName="is_available"
                    :checked="($room->is_available ?? true) == true" />
                <span id="availability-badge" class="badge f-11 ml-2"></span>
            </div>

            <div class="col-md-12 mt-3">
                <x-forms.textarea fieldId="notes" :fieldLabel="__('app.notes')" fieldName="notes" :fieldValue="$room->notes ?? ''" />
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <x-forms.button-cancel data-dismiss="modal" class="border-0 mr-3">@lang('app.cancel')</x-forms.button-cancel>
        <x-forms.button-primary id="update-room-form" icon="check">@lang('app.update')</x-forms.button-primary>
    </div>
</x-form>

<style>
    .ct-header-icon {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        background: rgba(0, 123, 255, .08);
        color: #007bff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 13px;
        flex-shrink: 0;
    }
    #update-room-form {
        transition: transform .12s ease, box-shadow .12s ease;
    }
    #update-room-form:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 10px rgba(0, 0, 0, .12);
    }
</style>

<script>
    // Original update handler — untouched, including the _method=PUT append
    $('#update-room-form').click(function() {
        $.easyAjax({
            url: "{{ route('hotel-rooms.update', $room->id) }}",
            container: '#roomEditForm',
            type: 'POST',
            data: $('#roomEditForm').serialize() + '&_method=PUT',
            success: function(response) {
                if (response.status === 'success') {
                    window.location.href = response.redirectUrl || '{{ route('hotel-rooms.index') }}';
                }
            }
        });
    });

    // Cosmetic-only additions below. Neither reads nor writes anything that
    // gets submitted — both are pure visual reflections of existing state.
    (function() {
        var typicalCapacity = {
            single: '1', double: '2', triple: '3', quad: '4', quint: '5'
        };
        var $roomType = $('#room_type');
        var $hint = $('#capacity-hint');

        function updateCapacityHint() {
            var typical = typicalCapacity[$roomType.val()];
            $hint.text(typical ? 'Typical capacity: ' + typical : '');
        }

        $roomType.on('change', updateCapacityHint);
        updateCapacityHint();
    })();

    (function() {
        var $checkbox = $('#is_available');
        var $badge = $('#availability-badge');

        function updateAvailabilityBadge() {
            var available = $checkbox.is(':checked');
            $badge
                .text(available ? '@lang('app.available')' : '@lang('app.unavailable')')
                .removeClass('badge-success badge-secondary')
                .addClass(available ? 'badge-success' : 'badge-secondary');
        }

        $checkbox.on('change', updateAvailabilityBadge);
        updateAvailabilityBadge();
    })();
</script>