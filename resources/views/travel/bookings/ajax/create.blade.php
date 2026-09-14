<div class="row">
    <div class="col-sm-12">
        <x-form id="save-data-form">
            <div class="add-client bg-white rounded shadow-sm">
                <h4 class="mb-0 p-20 f-21 font-weight-normal border-bottom-grey">
                    <i class="fa fa-suitcase-rolling mr-2 text-primary"></i>@lang('app.booking')
                </h4>

                <div class="p-20">
                    <p class="text-muted f-13 mb-4">Fields marked with an asterisk (*) are required.</p>

                    {{-- Group Information --}}
                    <h6 class="text-muted text-uppercase f-13 font-weight-bold mb-3">
                        <i class="fa fa-users mr-1"></i>Group Information
                    </h6>
                    <div class="row mb-2">
                        <div class="col-md-6">
                            <x-forms.text fieldId="group_name" :fieldLabel="__('modules.booking.groupName')" fieldName="group_name" fieldRequired="true"></x-forms.text>
                        </div>
                        <div class="col-md-6">
                            <x-forms.text fieldId="group_leader" :fieldLabel="__('modules.booking.groupLeader')" fieldName="group_leader"></x-forms.text>
                        </div>
                        <div class="col-md-6">
                            <x-forms.number fieldId="total_pax" :fieldLabel="__('modules.booking.totalPax')" fieldName="total_pax"></x-forms.number>
                        </div>
                    </div>

                    <hr class="my-4">

                    {{-- Travel Details --}}
                    <h6 class="text-muted text-uppercase f-13 font-weight-bold mb-3">
                        <i class="fa fa-plane-departure mr-1"></i>Travel Details
                    </h6>
                    <div class="row mb-2">
                        <div class="col-md-6">
                            <x-forms.select fieldId="package_id" :fieldLabel="__('app.package')" fieldName="package_id">
                                <option value="">--</option>
                                @foreach($packages as $package)
                                    <option value="{{ $package->id }}">{{ $package->name }}</option>
                                @endforeach
                            </x-forms.select>
                        </div>
                        <div class="col-md-6">
                            <x-forms.select fieldId="iata_id" :fieldLabel="__('app.iata')" fieldName="iata_id">
                                <option value="">--</option>
                                @foreach($iataRecords as $iata)
                                    <option value="{{ $iata->id }}">{{ $iata->name }} ({{ $iata->code }})</option>
                                @endforeach
                            </x-forms.select>
                        </div>
                        <div class="col-md-6">
                            <x-forms.datepicker fieldId="departure_date" :fieldLabel="__('app.departureDate')" fieldName="departure_date"></x-forms.datepicker>
                        </div>
                        <div class="col-md-6">
                            <x-forms.datepicker fieldId="return_date" :fieldLabel="__('app.returnDate')" fieldName="return_date"></x-forms.datepicker>
                        </div>
                    </div>

                    <hr class="my-4">

                    {{-- Status --}}
                    <h6 class="text-muted text-uppercase f-13 font-weight-bold mb-3">
                        <i class="fa fa-flag mr-1"></i>Status
                    </h6>
                    <div class="row">
                        <div class="col-md-6">
                            <x-forms.select fieldId="status" :fieldLabel="__('app.status')" fieldName="status">
                                <option value="pending">@lang('app.pending')</option>
                                <option value="confirmed">@lang('app.confirmed')</option>
                                <option value="cancelled">@lang('app.cancelled')</option>
                                <option value="completed">@lang('app.completed')</option>
                            </x-forms.select>
                        </div>
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
            url: "{{ route('bookings.store') }}", container: '#save-data-form', type: "POST",
            data: $('#save-data-form').serialize(), disableButton: true, blockUI: true,
            buttonSelector: "#save-form",
            success: function(response) { if (response.status == 'success') window.location.href = response.redirectUrl; }
        });
    });
</script>