<div class="modal-header">
    <h5 class="modal-title">@lang('app.addRoom')</h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
</div>
<x-form id="roomForm" method="POST" class="ajax-form">
    <div class="modal-body">
        <div class="row">
            <div class="col-md-6">
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
            <div class="col-md-4">
                <x-forms.select fieldId="room_type" :fieldLabel="__('app.roomType')" fieldName="room_type" fieldRequired="true">
                    <option value="single">@lang('app.single')</option>
                    <option value="double">@lang('app.double')</option>
                    <option value="triple">@lang('app.triple')</option>
                    <option value="quad">@lang('app.quad')</option>
                    <option value="quint">@lang('app.quint')</option>
                </x-forms.select>
            </div>
            <div class="col-md-4">
                <x-forms.number fieldId="capacity" :fieldLabel="__('app.capacity')" fieldName="capacity" fieldValue="2" fieldRequired="true" />
            </div>
            <div class="col-md-4">
                <x-forms.text fieldId="floor" :fieldLabel="__('app.floor')" fieldName="floor" />
            </div>
            <div class="col-md-6">
                <x-forms.select fieldId="gender_restriction" :fieldLabel="__('app.genderRestriction')" fieldName="gender_restriction">
                    <option value="">@lang('app.none')</option>
                    <option value="male">@lang('app.male')</option>
                    <option value="female">@lang('app.female')</option>
                </x-forms.select>
            </div>
            <div class="col-md-12">
                <x-forms.textarea fieldId="notes" :fieldLabel="__('app.notes')" fieldName="notes" />
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <x-forms.button-cancel data-dismiss="modal" class="border-0 mr-3">@lang('app.cancel')</x-forms.button-cancel>
        <x-forms.button-primary id="save-room-form" icon="check">@lang('app.save')</x-forms.button-primary>
    </div>
</x-form>

<script>
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
</script>
