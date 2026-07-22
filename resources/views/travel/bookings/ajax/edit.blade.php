<div class="row">
    <div class="col-sm-12">
        <x-form id="save-data-form" method="PUT">
            <div class="add-client bg-white rounded">
                <h4 class="mb-0 p-20 f-21 font-weight-normal border-bottom-grey">@lang('app.booking')</h4>
                <div class="row p-20">
                    <div class="col-md-6">
                        <x-forms.text fieldId="group_name" :fieldLabel="__('modules.booking.groupName')" fieldName="group_name" fieldRequired="true" fieldValue="{{ $booking->group_name }}"></x-forms.text>
                    </div>
                    <div class="col-md-6">
                        <x-forms.text fieldId="group_leader" :fieldLabel="__('modules.booking.groupLeader')" fieldName="group_leader" fieldValue="{{ $booking->group_leader }}"></x-forms.text>
                    </div>
                    <div class="col-md-6">
                        <x-forms.select fieldId="package_id" :fieldLabel="__('app.package')" fieldName="package_id">
                            <option value="">--</option>
                            @foreach($packages as $package)
                                <option value="{{ $package->id }}" {{ $booking->package_id == $package->id ? 'selected' : '' }}>{{ $package->name }}</option>
                            @endforeach
                        </x-forms.select>
                    </div>
                    <div class="col-md-6">
                        <x-forms.select fieldId="iata_id" :fieldLabel="__('app.iata')" fieldName="iata_id">
                            <option value="">--</option>
                            @foreach($iataRecords as $iata)
                                <option value="{{ $iata->id }}" {{ $booking->iata_id == $iata->id ? 'selected' : '' }}>{{ $iata->name }} ({{ $iata->iata_code }})</option>
                            @endforeach
                        </x-forms.select>
                    </div>
                    <div class="col-md-6">
                        <x-forms.number fieldId="total_pax" :fieldLabel="__('modules.booking.totalPax')" fieldName="total_pax" fieldValue="{{ $booking->total_pax }}"></x-forms.number>
                    </div>
                    <div class="col-md-6">
                        <x-forms.select fieldId="status" :fieldLabel="__('app.status')" fieldName="status">
                            <option value="pending" {{ $booking->status == 'pending' ? 'selected' : '' }}>@lang('app.pending')</option>
                            <option value="confirmed" {{ $booking->status == 'confirmed' ? 'selected' : '' }}>@lang('app.confirmed')</option>
                            <option value="cancelled" {{ $booking->status == 'cancelled' ? 'selected' : '' }}>@lang('app.cancelled')</option>
                            <option value="completed" {{ $booking->status == 'completed' ? 'selected' : '' }}>@lang('app.completed')</option>
                        </x-forms.select>
                    </div>
                    <div class="col-md-6">
                        <x-forms.datepicker fieldId="departure_date" :fieldLabel="__('app.departureDate')" fieldName="departure_date" fieldValue="{{ $booking->departure_date ? $booking->departure_date->format(company()->date_format) : '' }}"></x-forms.datepicker>
                    </div>
                    <div class="col-md-6">
                        <x-forms.datepicker fieldId="return_date" :fieldLabel="__('app.returnDate')" fieldName="return_date" fieldValue="{{ $booking->return_date ? $booking->return_date->format(company()->date_format) : '' }}"></x-forms.datepicker>
                    </div>
                </div>
                <x-form-actions>
                    <x-forms.button-primary id="save-form" class="mr-3" icon="check">@lang('app.save')</x-forms.button-primary>
                    <x-forms.button-cancel :link="route('bookings.index')" class="border-0">@lang('app.cancel')</x-forms.button-cancel>
                </x-form-actions>
            </div>
        </x-form>
    </div>
</div>
<script>
    $('#save-form').click(function() {
        $.easyAjax({
            url: "{{ route('bookings.update', $booking->id) }}", container: '#save-data-form', type: "POST",
            data: $('#save-data-form').serialize(), disableButton: true, blockUI: true,
            buttonSelector: "#save-form",
            success: function(response) { if (response.status == 'success') window.location.href = response.redirectUrl; }
        });
    });
</script>
