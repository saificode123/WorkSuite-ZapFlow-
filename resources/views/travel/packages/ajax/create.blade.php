<div class="row">
    <div class="col-sm-12">
        <x-form id="save-data-form">
            <div class="add-client bg-white rounded shadow-sm ct-card">
                <div class="p-20 border-bottom-grey d-flex align-items-center">
                    <div class="ct-header-icon mr-3">
                        <i class="fa fa-box"></i>
                    </div>
                    <h4 class="mb-0 f-21 font-weight-normal">@lang('app.package')</h4>
                </div>

                <div class="row p-20">
                    <div class="col-md-6 mb-3 mb-md-0">
                        <x-forms.text fieldId="name" :fieldLabel="__('app.name')" fieldName="name" fieldRequired="true"></x-forms.text>
                    </div>
                    <div class="col-md-6">
                        <x-forms.text fieldId="type" :fieldLabel="__('app.type')" fieldName="type"></x-forms.text>
                    </div>

                    <div class="col-md-6 mt-3">
                        <x-forms.number fieldId="duration_days" :fieldLabel="__('modules.package.durationDays')" fieldName="duration_days"></x-forms.number>
                    </div>
                    <div class="col-md-6 mt-3">
                        <x-forms.number fieldId="price" :fieldLabel="__('app.price')" fieldName="price"></x-forms.number>
                        <small id="price-per-day-hint" class="text-muted f-11 d-block mt-1"></small>
                    </div>

                    <div class="col-md-12 mt-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="mb-0 f-14">@lang('app.hotels')</label>
                            <span id="hotels-selected-badge" class="badge badge-light f-11"></span>
                        </div>
                        <x-forms.select fieldId="hotel_ids" fieldName="hotel_ids[]" multiple="true">
                            @foreach($hotels as $hotel)
                                <option value="{{ $hotel->id }}">{{ $hotel->name }}</option>
                            @endforeach
                        </x-forms.select>
                    </div>

                    <div class="col-md-12 mt-3">
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

<style>
    .ct-card { animation: ctFadeIn .2s ease-out; }
    @keyframes ctFadeIn {
        from { opacity: 0; transform: translateY(4px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    .ct-header-icon {
        width: 34px;
        height: 34px;
        border-radius: 8px;
        background: rgba(0, 123, 255, .08);
        color: #007bff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        flex-shrink: 0;
    }
    #save-form {
        transition: transform .12s ease, box-shadow .12s ease;
    }
    #save-form:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 10px rgba(0, 0, 0, .12);
    }
</style>

<script>
    // Original save handler — untouched
    $('#save-form').click(function() {
        $.easyAjax({
            url: "{{ route('packages.store') }}", container: '#save-data-form', type: "POST",
            data: $('#save-data-form').serialize(), disableButton: true, blockUI: true,
            buttonSelector: "#save-form",
            success: function(response) { if (response.status == 'success') window.location.href = response.redirectUrl; }
        });
    });

    // Cosmetic-only additions below. Neither reads nor writes anything that
    // gets submitted — both are pure visual reflections of existing state.
    (function() {
        var $price = $('#price');
        var $duration = $('#duration_days');
        var $hint = $('#price-per-day-hint');

        function updatePriceHint() {
            var price = parseFloat($price.val());
            var days = parseInt($duration.val(), 10);
            if (!price || !days || days <= 0) {
                $hint.text('');
                return;
            }
            $hint.text((price / days).toFixed(2) + ' / ' + '@lang('app.day')');
        }

        $price.add($duration).on('input', updatePriceHint);
        updatePriceHint();
    })();

    (function() {
        var $hotels = $('#hotel_ids');
        var $badge = $('#hotels-selected-badge');

        function updateHotelsBadge() {
            var count = $hotels.val() ? $hotels.val().length : 0;
            $badge.text(count > 0 ? count + ' ' + '@lang('app.selected')' : '');
        }

        $hotels.on('change', updateHotelsBadge);
        updateHotelsBadge();
    })();
</script>