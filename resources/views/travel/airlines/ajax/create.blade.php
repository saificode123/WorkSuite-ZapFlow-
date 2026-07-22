<div class="row">
    <div class="col-sm-12">
        <x-form id="save-data-form">
            <div class="add-client bg-white rounded">
                <h4 class="mb-0 p-20 f-21 font-weight-normal border-bottom-grey">@lang('app.airline')</h4>
                <div class="row p-20">
                    <div class="col-md-6">
                        <x-forms.text fieldId="name" :fieldLabel="__('app.name')" fieldName="name" fieldRequired="true"></x-forms.text>
                    </div>
                    <div class="col-md-6">
                        <x-forms.text fieldId="iata_code" :fieldLabel="__('app.iataCode')" fieldName="iata_code"></x-forms.text>
                    </div>
                    <div class="col-md-6">
                        <x-forms.email fieldId="contact_email" :fieldLabel="__('app.email')" fieldName="contact_email"></x-forms.email>
                    </div>
                    <div class="col-md-6">
                        <x-forms.text fieldId="contact_phone" :fieldLabel="__('app.phone')" fieldName="contact_phone"></x-forms.text>
                    </div>
                    <div class="col-md-6">
                        <x-forms.url fieldId="website" :fieldLabel="__('app.website')" fieldName="website"></x-forms.url>
                    </div>
                    <div class="col-md-6">
                        <x-forms.text fieldId="logo" :fieldLabel="__('app.logo')" fieldName="logo"></x-forms.text>
                    </div>
                </div>
                <x-form-actions>
                    <x-forms.button-primary id="save-form" class="mr-3" icon="check">@lang('app.save')</x-forms.button-primary>
                    <x-forms.button-cancel :link="route('airlines.index')" class="border-0">@lang('app.cancel')</x-forms.button-cancel>
                </x-form-actions>
            </div>
        </x-form>
    </div>
</div>
<script>
    $('#save-form').click(function() {
        $.easyAjax({
            url: "{{ route('airlines.store') }}", container: '#save-data-form', type: "POST",
            data: $('#save-data-form').serialize(), disableButton: true, blockUI: true,
            buttonSelector: "#save-form",
            success: function(response) { if (response.status == 'success') window.location.href = response.redirectUrl; }
        });
    });
</script>
