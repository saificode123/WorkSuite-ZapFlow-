<div class="modal-header">
    <div class="d-flex align-items-center">
        <div class="ct-header-icon mr-3">
            <i class="fa fa-bed"></i>
        </div>
        <h5 class="modal-title mb-0">@lang('app.addRoom')</h5>
    </div>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
</div>
<x-form id="roomForm" method="POST" class="ajax-form">
    <div class="modal-body">
        <div class="row">
            <div class="col-md-6 mb-3 mb-md-0">
                <x-forms.select fieldId="hotel_id" :fieldLabel="__('app.hotel')" fieldName="hotel_id" fieldRequired="true" search="true">
                    <option value="">--</option>
                    @foreach($hotels ?? \App\Models\Hotel::orderBy('name')->get() as $hotel)
                        <option value="{{ $hotel->id }}">{{ $hotel->name }}</option>
                    @endforeach
                </x-forms.select>
            </div>
            <div class="col-md-6">
                <x-forms.text fieldId="room_number" :fieldLabel="__('app.roomNumber')" fieldName="room_number" />
            </div>

            <div class="col-md-4 mt-3">
                <x-forms.select fieldId="room_type" :fieldLabel="__('app.roomType')" fieldName="room_type" fieldRequired="true">
                    <option value="single">@lang('app.single')</option>
                    <option value="double">@lang('app.double')</option>
                    <option value="triple">@lang('app.triple')</option>
                    <option value="quad">@lang('app.quad')</option>
                    <option value="quint">@lang('app.quint')</option>
                </x-forms.select>
            </div>
            <div class="col-md-4 mt-3">
                <x-forms.number fieldId="capacity" :fieldLabel="__('app.capacity')" fieldName="capacity" fieldValue="2" fieldRequired="true" />
                <small id="capacity-hint" class="text-muted f-11 d-block mt-1"></small>
            </div>
            <div class="col-md-4 mt-3">
                <x-forms.text fieldId="floor" :fieldLabel="__('app.floor')" fieldName="floor" />
            </div>

            <div class="col-md-6 mt-3">
                <x-forms.select fieldId="gender_restriction" :fieldLabel="__('app.genderRestriction')" fieldName="gender_restriction">
                    <option value="">@lang('app.none')</option>
                    <option value="male">@lang('app.male')</option>
                    <option value="female">@lang('app.female')</option>
                </x-forms.select>
            </div>
            <div class="col-md-12 mt-3">
                <x-forms.textarea fieldId="notes" :fieldLabel="__('app.notes')" fieldName="notes" />
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <x-forms.button-cancel data-dismiss="modal" class="border-0 mr-3">@lang('app.cancel')</x-forms.button-cancel>
        <x-forms.button-primary id="save-room-form" icon="check">@lang('app.save')</x-forms.button-primary>
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
    #save-room-form {
        transition: transform .12s ease, box-shadow .12s ease;
    }
    #save-room-form:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 10px rgba(0, 0, 0, .12);
    }
</style>

<script>
    // Original save handler — untouched
    $('#save-room-form').click(function() {
        $.easyAjax({
            url: "{{ route('hotel-rooms.store') }}",
            container: '#roomForm',
            type: 'POST',
            data: $('#roomForm').serialize(),
            success: function(response) {
                if (response.status === 'success') {
                    window.location.href = response.redirectUrl || '{{ route('hotel-rooms.index') }}';
                }
            }
        });
    });

    // Cosmetic-only addition: shows a typical-occupancy hint next to Capacity
    // based on the selected Room Type. It never sets or overrides the
    // capacity value itself — just a suggestion the user can ignore.
    (function() {
        var typicalCapacity = {
            single: '1', double: '2', triple: '3', quad: '4', quint: '5'
        };
        var $roomType = $('#room_type');
        var $hint = $('#capacity-hint');

        function updateHint() {
            var typical = typicalCapacity[$roomType.val()];
            $hint.text(typical ? 'Typical capacity: ' + typical : '');
        }

        $roomType.on('change', updateHint);
        updateHint();
    })();
</script>