<div class="row">
    <div class="col-sm-12">
        <x-form id="save-data-form" method="PUT">
            <div class="add-client bg-white rounded">
                <h4 class="mb-0 p-20 f-21 font-weight-normal border-bottom-grey">@lang('app.hotel')</h4>
                <div class="row p-20">
                    <div class="col-md-6">
                        <x-forms.text fieldId="name" :fieldLabel="__('app.name')" fieldName="name" fieldRequired="true" fieldValue="{{ $hotel->name }}"></x-forms.text>
                    </div>
                    <div class="col-md-6">
                        <x-forms.number fieldId="star_rating" :fieldLabel="__('modules.hotel.starRating')" fieldName="star_rating" min="1" max="5" fieldValue="{{ $hotel->star_rating }}"></x-forms.number>
                    </div>
                    <div class="col-md-6">
                        <x-forms.text fieldId="city" :fieldLabel="__('app.city')" fieldName="city" fieldValue="{{ $hotel->city }}"></x-forms.text>
                    </div>
                    <div class="col-md-6">
                        <x-forms.text fieldId="country" :fieldLabel="__('app.country')" fieldName="country" fieldValue="{{ $hotel->country }}"></x-forms.text>
                    </div>
                    <div class="col-md-6">
                        <x-forms.email fieldId="contact_email" :fieldLabel="__('app.email')" fieldName="contact_email" fieldValue="{{ $hotel->contact_email }}"></x-forms.email>
                    </div>
                    <div class="col-md-6">
                        <x-forms.text fieldId="contact_phone" :fieldLabel="__('app.phone')" fieldName="contact_phone" fieldValue="{{ $hotel->contact_phone }}"></x-forms.text>
                    </div>
                </div>
                <x-form-actions>
                    <x-forms.button-primary id="save-form" class="mr-3" icon="check">@lang('app.save')</x-forms.button-primary>
                    <x-forms.button-cancel :link="route('hotels.index')" class="border-0">@lang('app.cancel')</x-forms.button-cancel>
                </x-form-actions>
            </div>
        </x-form>
    </div>
</div>
<script>
    $('#save-form').click(function() {
        $.easyAjax({
            url: "{{ route('hotels.update', $hotel->id) }}", container: '#save-data-form', type: "POST",
            data: $('#save-data-form').serialize(), disableButton: true, blockUI: true,
            buttonSelector: "#save-form",
            success: function(response) { if (response.status == 'success') window.location.href = response.redirectUrl; }
        });
    });
</script>
