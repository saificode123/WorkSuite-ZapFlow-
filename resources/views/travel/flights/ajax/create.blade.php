<div class="row">
    <div class="col-sm-12">
        <x-form id="save-data-form">
            <div class="add-client bg-white rounded">
                <h4 class="mb-0 p-20 f-21 font-weight-normal border-bottom-grey">@lang('app.flight')</h4>
                <div class="row p-20">
                    <div class="col-md-6">
                        <x-forms.text fieldId="flight_number" :fieldLabel="__('modules.flight.flightNumber')" fieldName="flight_number" fieldRequired="true"></x-forms.text>
                    </div>
                    <div class="col-md-6">
                        <x-forms.select fieldId="airline_id" :fieldLabel="__('app.airline')" fieldName="airline_id">
                            <option value="">--</option>
                            @foreach($airlines as $airline)
                                <option value="{{ $airline->id }}">{{ $airline->name }}</option>
                            @endforeach
                        </x-forms.select>
                    </div>
                    <div class="col-md-6">
                        <x-forms.text fieldId="origin" :fieldLabel="__('app.origin')" fieldName="origin" fieldRequired="true"></x-forms.text>
                    </div>
                    <div class="col-md-6">
                        <x-forms.text fieldId="destination" :fieldLabel="__('app.destination')" fieldName="destination" fieldRequired="true"></x-forms.text>
                    </div>
                    <div class="col-md-6">
                        <x-forms.time fieldId="departure_time" :fieldLabel="__('modules.flight.departureTime')" fieldName="departure_time"></x-forms.time>
                    </div>
                    <div class="col-md-6">
                        <x-forms.time fieldId="arrival_time" :fieldLabel="__('modules.flight.arrivalTime')" fieldName="arrival_time"></x-forms.time>
                    </div>
                </div>
                <x-form-actions>
                    <x-forms.button-primary id="save-form" class="mr-3" icon="check">@lang('app.save')</x-forms.button-primary>
                    <x-forms.button-cancel :link="route('flights.index')" class="border-0">@lang('app.cancel')</x-forms.button-cancel>
                </x-form-actions>
            </div>
        </x-form>
    </div>
</div>
<script>
    $('#save-form').click(function() {
        $.easyAjax({
            url: "{{ route('flights.store') }}", container: '#save-data-form', type: "POST",
            data: $('#save-data-form').serialize(), disableButton: true, blockUI: true,
            buttonSelector: "#save-form",
            success: function(response) { if (response.status == 'success') window.location.href = response.redirectUrl; }
        });
    });
</script>
