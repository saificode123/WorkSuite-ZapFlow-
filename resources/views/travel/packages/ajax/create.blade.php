<div class="row">
    <div class="col-sm-12">
        <x-form id="save-data-form">
            <div class="add-client bg-white rounded">
                <h4 class="mb-0 p-20 f-21 font-weight-normal border-bottom-grey">@lang('app.package')</h4>
                <div class="row p-20">
                    <div class="col-md-6">
                        <x-forms.text fieldId="name" :fieldLabel="__('app.name')" fieldName="name" fieldRequired="true"></x-forms.text>
                    </div>
                    <div class="col-md-6">
                        <x-forms.text fieldId="type" :fieldLabel="__('app.type')" fieldName="type"></x-forms.text>
                    </div>
                    <div class="col-md-6">
                        <x-forms.number fieldId="duration_days" :fieldLabel="__('modules.package.durationDays')" fieldName="duration_days"></x-forms.number>
                    </div>
                    <div class="col-md-6">
                        <x-forms.number fieldId="price" :fieldLabel="__('app.price')" fieldName="price"></x-forms.number>
                    </div>
                    <div class="col-md-12">
                        <x-forms.select fieldId="hotel_ids" :fieldLabel="__('app.hotels')" fieldName="hotel_ids[]" multiple="true">
                            @foreach($hotels as $hotel)
                                <option value="{{ $hotel->id }}">{{ $hotel->name }}</option>
                            @endforeach
                        </x-forms.select>
                    </div>
                    <div class="col-md-12">
                        <x-forms.textarea fieldId="description" :fieldLabel="__('app.description')" fieldName="description"></x-forms.textarea>
                    </div>
                </div>
                <x-form-actions>
                    <x-forms.button-primary id="save-form" class="mr-3" icon="check">@lang('app.save')</x-forms.button-primary>
                    <x-forms.button-cancel :link="route('packages.index')" class="border-0">@lang('app.cancel')</x-forms.button-cancel>
                </x-form-actions>
            </div>
        </x-form>
    </div>
</div>
<script>
    $('#save-form').click(function() {
        $.easyAjax({
            url: "{{ route('packages.store') }}", container: '#save-data-form', type: "POST",
            data: $('#save-data-form').serialize(), disableButton: true, blockUI: true,
            buttonSelector: "#save-form",
            success: function(response) { if (response.status == 'success') window.location.href = response.redirectUrl; }
        });
    });
</script>
