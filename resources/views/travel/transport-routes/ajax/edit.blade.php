<div class="row">
    <div class="col-sm-12">
        <x-form id="save-data-form" method="PUT">
            <div class="add-client bg-white rounded shadow-sm">
                <h4 class="mb-0 p-20 f-21 font-weight-normal border-bottom-grey">
                    <i class="fa fa-route mr-2 text-primary"></i>@lang('app.transportRoute')
                </h4>
                <div class="p-20">
                    <p class="text-muted f-13 mb-4">Fields marked with an asterisk (*) are required.</p>
                    <div class="row">
                        <div class="col-md-6">
                            <x-forms.text fieldId="from_city" :fieldLabel="__('app.fromCity')" fieldName="from_city" fieldRequired="true" fieldValue="{{ $transportRoute->from_city }}"></x-forms.text>
                        </div>
                        <div class="col-md-6">
                            <x-forms.text fieldId="to_city" :fieldLabel="__('app.toCity')" fieldName="to_city" fieldRequired="true" fieldValue="{{ $transportRoute->to_city }}"></x-forms.text>
                        </div>
                        <div class="col-md-6">
                            <x-forms.number fieldId="distance_km" :fieldLabel="__('modules.transportRoute.distanceKm')" fieldName="distance_km" fieldValue="{{ $transportRoute->distance_km }}"></x-forms.number>
                        </div>
                    </div>
                </div>
                <x-form-actions>
                    <x-forms.button-primary id="save-form" class="mr-3" icon="check">@lang('app.save')</x-forms.button-primary>
                    <x-forms.button-cancel :link="route('transport-routes.index')" class="border-0">@lang('app.cancel')</x-forms.button-cancel>
                </x-form-actions>
            </div>
        </x-form>
    </div>
</div>

<script>
    $('#save-form').click(function() {
        $.easyAjax({
            url: "{{ route('transport-routes.update', $transportRoute->id) }}", container: '#save-data-form', type: "POST",
            data: $('#save-data-form').serialize(), disableButton: true, blockUI: true,
            buttonSelector: "#save-form",
            success: function(response) { if (response.status == 'success') window.location.href = response.redirectUrl; }
        });
    });
</script>