<div class="row">
    <div class="col-sm-12">
        <x-form id="save-data-form" method="PUT">
            <div class="add-client bg-white rounded shadow-sm">
                <h4 class="mb-0 p-20 f-21 font-weight-normal border-bottom-grey">
                    <i class="fa fa-passport mr-2 text-primary"></i>@lang('app.visaCompany')
                </h4>
                <div class="p-20">
                    <p class="text-muted f-13 mb-4">Fields marked with an asterisk (*) are required.</p>
                    <div class="row">
                        <div class="col-md-6">
                            <x-forms.text fieldId="name" :fieldLabel="__('app.name')" fieldName="name" fieldRequired="true" fieldValue="{{ $visaCompany->name }}"></x-forms.text>
                        </div>
                        <div class="col-md-6">
                            <x-forms.text fieldId="country" :fieldLabel="__('app.country')" fieldName="country" fieldRequired="true" fieldValue="{{ $visaCompany->country }}"></x-forms.text>
                        </div>
                        <div class="col-md-6">
                            <x-forms.number fieldId="fee" :fieldLabel="__('app.fee')" fieldName="fee" fieldValue="{{ $visaCompany->fee }}"></x-forms.number>
                        </div>
                        <div class="col-md-6">
                            <x-forms.number fieldId="processing_days" :fieldLabel="__('modules.visaCompany.processingDays')" fieldName="processing_days" fieldValue="{{ $visaCompany->processing_days }}"></x-forms.number>
                        </div>
                    </div>
                </div>
                <x-form-actions>
                    <x-forms.button-primary id="save-form" class="mr-3" icon="check">@lang('app.save')</x-forms.button-primary>
                    <x-forms.button-cancel :link="route('visa-companies.index')" class="border-0">@lang('app.cancel')</x-forms.button-cancel>
                </x-form-actions>
            </div>
        </x-form>
    </div>
</div>

<script>
    $('#save-form').click(function() {
        $.easyAjax({
            url: "{{ route('visa-companies.update', $visaCompany->id) }}", container: '#save-data-form', type: "POST",
            data: $('#save-data-form').serialize(), disableButton: true, blockUI: true,
            buttonSelector: "#save-form",
            success: function(response) { if (response.status == 'success') window.location.href = response.redirectUrl; }
        });
    });
</script>