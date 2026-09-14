<div class="row">
    <div class="col-sm-12">
        <x-form id="save-data-form">
            <div class="add-client bg-white rounded shadow-sm">
                <h4 class="mb-0 p-20 f-21 font-weight-normal border-bottom-grey">
                    <i class="fa fa-plane mr-2 text-primary"></i>@lang('app.airline')
                </h4>

                <div class="p-20">
                    <p class="text-muted f-13 mb-4">Fields marked with an asterisk (*) are required.</p>

                    {{-- Basic Information --}}
                    <h6 class="text-muted text-uppercase f-13 font-weight-bold mb-3">
                        <i class="fa fa-info-circle mr-1"></i>Basic Information
                    </h6>
                    <div class="row mb-2">
                        <div class="col-md-6">
                            <x-forms.text fieldId="name" :fieldLabel="__('app.name')" fieldName="name" fieldRequired="true"></x-forms.text>
                        </div>
                        <div class="col-md-6">
                            <x-forms.text fieldId="code" :fieldLabel="__('app.iataCode')" fieldName="code"></x-forms.text>
                        </div>
                        <div class="col-md-6">
                            <x-forms.text fieldId="logo" :fieldLabel="__('app.logo')" fieldName="logo"></x-forms.text>
                        </div>
                    </div>

                    <hr class="my-4">

                    {{-- Contact Information --}}
                    <h6 class="text-muted text-uppercase f-13 font-weight-bold mb-3">
                        <i class="fa fa-address-book mr-1"></i>Contact Information
                    </h6>
                    <div class="row">
                        <div class="col-md-6">
                            <x-forms.email fieldId="email" :fieldLabel="__('app.email')" :fieldPlaceholder="__('app.email')" fieldName="email"></x-forms.email>
                        </div>
                        <div class="col-md-6">
                            <x-forms.text fieldId="phone" :fieldLabel="__('app.phone')" fieldName="phone"></x-forms.text>
                        </div>
                        <div class="col-md-6">
                            <x-forms.url fieldId="website" :fieldLabel="__('app.website')" fieldName="website"></x-forms.url>
                        </div>
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