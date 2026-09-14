<div class="row">
    <div class="col-sm-12">
        <x-form id="save-data-form" method="PUT">
            <div class="add-client bg-white rounded shadow-sm">

                {{-- Header --}}
                <div class="d-flex align-items-center justify-content-between p-20 border-bottom-grey">
                    <div class="d-flex align-items-center">
                        <div class="icon-circle bg-light-primary text-primary mr-3">
                            <i class="fa fa-box" aria-hidden="true"></i>
                        </div>
                        <div>
                            <h4 class="mb-0 f-21 font-weight-normal">@lang('app.package')</h4>
                            <small class="text-muted">{{ $package->name ?? __('app.package') }}</small>
                        </div>
                    </div>
                </div>

                <div class="p-20">

                    {{-- Basic info --}}
                    <h6 class="text-uppercase text-muted f-13 font-weight-bold mb-3">
                        <i class="fa fa-info-circle mr-1"></i> @lang('app.basicInformation')
                    </h6>
                    <div class="row">
                        <div class="col-md-6">
                            <x-forms.text fieldId="name" :fieldLabel="__('app.name')" fieldName="name" fieldRequired="true" fieldValue="{{ $package->name }}"></x-forms.text>
                        </div>
                        <div class="col-md-6">
                            <x-forms.text fieldId="type" :fieldLabel="__('app.type')" fieldName="type" fieldValue="{{ $package->type }}"></x-forms.text>
                        </div>
                    </div>

                    <hr class="my-4">

                    {{-- Duration & Pricing --}}
                    <h6 class="text-uppercase text-muted f-13 font-weight-bold mb-3">
                        <i class="fa fa-tags mr-1"></i> @lang('modules.package.durationDays') &amp; @lang('app.price')
                    </h6>
                    <div class="row">
                        <div class="col-md-6">
                            <x-forms.number fieldId="duration_days" :fieldLabel="__('modules.package.durationDays')" fieldName="duration_days" fieldValue="{{ $package->duration_days }}"></x-forms.number>
                            <small class="text-muted">@lang('modules.package.durationDaysHelp', ['default' => 'Total number of days this package is valid for.'])</small>
                        </div>
                        <div class="col-md-6">
                            <x-forms.number fieldId="price" :fieldLabel="__('app.price')" fieldName="price" fieldValue="{{ $package->price }}"></x-forms.number>
                        </div>
                    </div>

                    <hr class="my-4">

                    {{-- Hotels --}}
                    <h6 class="text-uppercase text-muted f-13 font-weight-bold mb-3 d-flex align-items-center justify-content-between">
                        <span><i class="fa fa-hotel mr-1"></i> @lang('app.hotels')</span>
                        <span class="badge badge-light-primary" id="hotel-selected-count">
                            {{ $package->hotels->count() }} @lang('app.selected')
                        </span>
                    </h6>
                    <div class="row">
                        <div class="col-md-12">
                            <x-forms.select fieldId="hotel_ids" :fieldLabel="__('app.hotels')" fieldName="hotel_ids[]" multiple="true">
                                @foreach($hotels as $hotel)
                                    <option value="{{ $hotel->id }}" {{ in_array($hotel->id, $package->hotels->pluck('id')->toArray()) ? 'selected' : '' }}>{{ $hotel->name }}</option>
                                @endforeach
                            </x-forms.select>
                            <small class="text-muted">@lang('modules.package.hotelsHelp', ['default' => 'You can select multiple hotels for this package.'])</small>
                        </div>
                    </div>

                    <hr class="my-4">

                    {{-- Description --}}
                    <h6 class="text-uppercase text-muted f-13 font-weight-bold mb-3">
                        <i class="fa fa-align-left mr-1"></i> @lang('app.description')
                    </h6>
                    <div class="row">
                        <div class="col-md-12">
                            <x-forms.textarea fieldId="description" :fieldLabel="__('app.description')" fieldName="description">{{ $package->description }}</x-forms.textarea>
                        </div>
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

<style>
    .icon-circle {
        width: 42px;
        height: 42px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
    }
    .bg-light-primary { background-color: rgba(13, 110, 253, 0.1); }
    .badge-light-primary {
        background-color: rgba(13, 110, 253, 0.1);
        color: #0d6efd;
        font-weight: 500;
        padding: 4px 10px;
        border-radius: 12px;
    }
</style>

<script>
    $('#save-form').click(function() {
        $.easyAjax({
            url: "{{ route('packages.update', $package->id) }}", container: '#save-data-form', type: "POST",
            data: $('#save-data-form').serialize(), disableButton: true, blockUI: true,
            buttonSelector: "#save-form",
            success: function(response) { if (response.status == 'success') window.location.href = response.redirectUrl; }
        });
    });

    // Cosmetic only: keeps the "selected hotels" badge in sync, does not affect submission
    $('#hotel_ids').on('change', function() {
        var count = $(this).find('option:selected').length;
        $('#hotel-selected-count').text(count + ' ' + '@lang('app.selected')');
    });
</script>