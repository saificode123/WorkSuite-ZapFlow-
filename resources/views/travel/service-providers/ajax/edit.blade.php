<div class="row">
    <div class="col-sm-12">
        <x-form id="save-data-form" method="PUT">
            <div class="add-client bg-white rounded">
                <h4 class="mb-0 p-20 f-21 font-weight-normal border-bottom-grey">@lang('app.serviceProvider')</h4>
                <div class="row p-20">
                    <div class="col-md-6">
                        <x-forms.text fieldId="name" :fieldLabel="__('app.name')" fieldName="name" fieldRequired="true" fieldValue="{{ $serviceProvider->name }}"></x-forms.text>
                    </div>
                    <div class="col-md-6">
                        <x-forms.select fieldId="type" :fieldLabel="__('app.type')" fieldName="type" fieldRequired="true">
                            <option value="hotel" {{ $serviceProvider->type == 'hotel' ? 'selected' : '' }}>@lang('app.hotel')</option>
                            <option value="transporter" {{ $serviceProvider->type == 'transporter' ? 'selected' : '' }}>@lang('app.transporter')</option>
                            <option value="airline" {{ $serviceProvider->type == 'airline' ? 'selected' : '' }}>@lang('app.airline')</option>
                            <option value="visa" {{ $serviceProvider->type == 'visa' ? 'selected' : '' }}>@lang('app.visa')</option>
                            <option value="iata" {{ $serviceProvider->type == 'iata' ? 'selected' : '' }}>@lang('app.iata')</option>
                            <option value="other" {{ $serviceProvider->type == 'other' ? 'selected' : '' }}>@lang('app.other')</option>
                        </x-forms.select>
                    </div>
                    <div class="col-md-6">
                        <x-forms.text fieldId="contact_person" :fieldLabel="__('app.contactPerson')" fieldName="contact_person" fieldValue="{{ $serviceProvider->contact_person }}"></x-forms.text>
                    </div>
                    <div class="col-md-6">
                        <x-forms.email fieldId="email" :fieldLabel="__('app.email')" fieldName="email" fieldValue="{{ $serviceProvider->email }}"></x-forms.email>
                    </div>
                    <div class="col-md-6">
                        <x-forms.text fieldId="phone" :fieldLabel="__('app.phone')" fieldName="phone" fieldValue="{{ $serviceProvider->phone }}"></x-forms.text>
                    </div>
                    <div class="col-md-6">
                        <x-forms.select fieldId="status" :fieldLabel="__('app.status')" fieldName="status">
                            <option value="active" {{ $serviceProvider->status == 'active' ? 'selected' : '' }}>@lang('app.active')</option>
                            <option value="inactive" {{ $serviceProvider->status == 'inactive' ? 'selected' : '' }}>@lang('app.inactive')</option>
                        </x-forms.select>
                    </div>
                    <div class="col-md-12">
                        <x-forms.textarea fieldId="address" :fieldLabel="__('app.address')" fieldName="address">{{ $serviceProvider->address }}</x-forms.textarea>
                    </div>
                </div>
                <x-form-actions>
                    <x-forms.button-primary id="save-form" class="mr-3" icon="check">@lang('app.save')</x-forms.button-primary>
                    <x-forms.button-cancel :link="route('service-providers.index')" class="border-0">@lang('app.cancel')</x-forms.button-cancel>
                </x-form-actions>
            </div>
        </x-form>
    </div>
</div>
<script>
    $('#save-form').click(function() {
        $.easyAjax({
            url: "{{ route('service-providers.update', $serviceProvider->id) }}", container: '#save-data-form', type: "POST",
            data: $('#save-data-form').serialize(), disableButton: true, blockUI: true,
            buttonSelector: "#save-form",
            success: function(response) { if (response.status == 'success') window.location.href = response.redirectUrl; }
        });
    });
</script>
