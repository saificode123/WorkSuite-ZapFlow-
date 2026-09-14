<div class="row">
    <div class="col-sm-12">
        <x-form id="save-data-form">
            <div class="add-client bg-white rounded shadow-sm">
                <h4 class="mb-0 p-20 f-21 font-weight-normal border-bottom-grey">
                    <i class="fa fa-tags mr-2 text-primary"></i>@lang('app.customerType')
                </h4>
                <div class="p-20">
                    <p class="text-muted f-13 mb-4">Fields marked with an asterisk (*) are required.</p>
                    <div class="row">
                        <div class="col-md-6">
                            <x-forms.text fieldId="name" :fieldLabel="__('app.name')" fieldName="name" fieldRequired="true" :fieldPlaceholder="__('placeholders.name')"></x-forms.text>
                        </div>
                        <div class="col-md-6">
                            <x-forms.text fieldId="description" :fieldLabel="__('app.description')" fieldName="description" :fieldPlaceholder="__('placeholders.description')"></x-forms.text>
                        </div>
                        <div class="col-md-12">
                            <x-forms.textarea fieldId="config_json" :fieldLabel="__('app.configJson')" fieldName="config_json" :fieldPlaceholder="__('placeholders.configJson')"></x-forms.textarea>
                            <small class="text-muted">Enter a valid JSON object.</small>
                        </div>
                    </div>
                </div>
                <x-form-actions>
                    <x-forms.button-primary id="save-form" class="mr-3" icon="check">@lang('app.save')</x-forms.button-primary>
                    <x-forms.button-cancel :link="route('customer-types.index')" class="border-0">@lang('app.cancel')</x-forms.button-cancel>
                </x-form-actions>
            </div>
        </x-form>
    </div>
</div>

<script>
    $('#save-form').click(function() {
        $.easyAjax({
            url: "{{ route('customer-types.store') }}", container: '#save-data-form', type: "POST",
            data: $('#save-data-form').serialize(), disableButton: true, blockUI: true,
            buttonSelector: "#save-form",
            success: function(response) { if (response.status == 'success') window.location.href = response.redirectUrl; }
        });
    });
</script>