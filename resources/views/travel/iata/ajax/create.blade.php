<div class="row">
    <div class="col-sm-12">
        <x-form id="save-data-form">
            <div class="add-client bg-white rounded">
                <h4 class="mb-0 p-20 f-21 font-weight-normal border-bottom-grey">@lang('app.iata')</h4>
                <div class="row p-20">
                    <div class="col-md-6">
                        <x-forms.text fieldId="iata_code" :fieldLabel="__('app.iataCode')" fieldName="iata_code" fieldRequired="true" :fieldPlaceholder="__('placeholders.iataCode')"></x-forms.text>
                    </div>
                    <div class="col-md-6">
                        <x-forms.text fieldId="name" :fieldLabel="__('app.name')" fieldName="name" fieldRequired="true" :fieldPlaceholder="__('placeholders.name')"></x-forms.text>
                    </div>
                    <div class="col-md-6">
                        <x-forms.select fieldId="status" :fieldLabel="__('app.status')" fieldName="status">
                            <option value="active">@lang('app.active')</option>
                            <option value="inactive">@lang('app.inactive')</option>
                        </x-forms.select>
                    </div>
                </div>
                <x-form-actions>
                    <x-forms.button-primary id="save-form" class="mr-3" icon="check">@lang('app.save')</x-forms.button-primary>
                    <x-forms.button-cancel :link="route('iata.index')" class="border-0">@lang('app.cancel')</x-forms.button-cancel>
                </x-form-actions>
            </div>
        </x-form>
    </div>
</div>
<script>
    $('#save-form').click(function() {
        $.easyAjax({
            url: "{{ route('iata.store') }}", container: '#save-data-form', type: "POST",
            data: $('#save-data-form').serialize(), disableButton: true, blockUI: true,
            buttonSelector: "#save-form",
            success: function(response) { if (response.status == 'success') window.location.href = response.redirectUrl; }
        });
    });
</script>
