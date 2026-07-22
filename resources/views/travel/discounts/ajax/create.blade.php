<div class="row">
    <div class="col-sm-12">
        <x-form id="save-data-form">
            <div class="add-client bg-white rounded">
                <h4 class="mb-0 p-20 f-21 font-weight-normal border-bottom-grey">@lang('app.discount')</h4>
                <div class="row p-20">
                    <div class="col-md-6">
                        <x-forms.text fieldId="name" :fieldLabel="__('app.name')" fieldName="name" fieldRequired="true"></x-forms.text>
                    </div>
                    <div class="col-md-6">
                        <x-forms.select fieldId="type" :fieldLabel="__('app.type')" fieldName="type" fieldRequired="true">
                            <option value="percentage">@lang('app.percentage')</option>
                            <option value="fixed">@lang('app.fixed')</option>
                        </x-forms.select>
                    </div>
                    <div class="col-md-6">
                        <x-forms.number fieldId="value" :fieldLabel="__('app.value')" fieldName="value" fieldRequired="true"></x-forms.number>
                    </div>
                    <div class="col-md-6">
                        <x-forms.select fieldId="is_active" :fieldLabel="__('app.active')" fieldName="is_active">
                            <option value="1">@lang('app.yes')</option>
                            <option value="0">@lang('app.no')</option>
                        </x-forms.select>
                    </div>
                </div>
                <x-form-actions>
                    <x-forms.button-primary id="save-form" class="mr-3" icon="check">@lang('app.save')</x-forms.button-primary>
                    <x-forms.button-cancel :link="route('discounts.index')" class="border-0">@lang('app.cancel')</x-forms.button-cancel>
                </x-form-actions>
            </div>
        </x-form>
    </div>
</div>
<script>
    $('#save-form').click(function() {
        $.easyAjax({
            url: "{{ route('discounts.store') }}", container: '#save-data-form', type: "POST",
            data: $('#save-data-form').serialize(), disableButton: true, blockUI: true,
            buttonSelector: "#save-form",
            success: function(response) { if (response.status == 'success') window.location.href = response.redirectUrl; }
        });
    });
</script>
