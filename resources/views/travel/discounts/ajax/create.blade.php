<div class="row">
    <div class="col-sm-12">
        <x-form id="save-data-form">
            <div class="add-client bg-white rounded shadow-sm ct-card">
                <div class="p-20 border-bottom-grey d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center">
                        <div class="ct-header-icon mr-3">
                            <i class="fa fa-percent"></i>
                        </div>
                        <h4 class="mb-0 f-21 font-weight-normal">@lang('app.discount')</h4>
                    </div>
                    <span id="discount-preview-badge" class="badge badge-light f-12 d-none d-sm-inline-block"></span>
                </div>

                <div class="row p-20">
                    <div class="col-md-6 mb-3 mb-md-0">
                        <x-forms.text fieldId="name" :fieldLabel="__('app.name')" fieldName="name" fieldRequired="true"></x-forms.text>
                    </div>
                    <div class="col-md-6">
                        <x-forms.select fieldId="discount_type" :fieldLabel="__('app.type')" fieldName="discount_type" fieldRequired="true">
                            <option value="percentage">@lang('app.percentage')</option>
                            <option value="fixed">@lang('app.fixed')</option>
                        </x-forms.select>
                    </div>
                    <div class="col-md-6 mt-3">
                        <x-forms.number fieldId="value" :fieldLabel="__('app.value')" fieldName="value" fieldRequired="true"></x-forms.number>
                    </div>
                    <div class="col-md-6 mt-3">
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
    #discount-preview-badge {
        border: 1px solid rgba(0, 0, 0, .1);
    }
</style>

<script>
    // Original save handler — untouched
    $('#save-form').click(function() {
        $.easyAjax({
            url: "{{ route('discounts.store') }}", container: '#save-data-form', type: "POST",
            data: $('#save-data-form').serialize(), disableButton: true, blockUI: true,
            buttonSelector: "#save-form",
            success: function(response) { if (response.status == 'success') window.location.href = response.redirectUrl; }
        });
    });

    // Cosmetic-only addition: live preview badge, built entirely from text
    // already present in the dropdowns. Never touches submission or values.
    (function() {
        var $value = $('#value');
        var $type = $('#discount_type');
        var $badge = $('#discount-preview-badge');

        function updatePreview() {
            var val = $.trim($value.val());
            var typeText = $type.find('option:selected').text();
            if (val === '' || isNaN(val)) {
                $badge.text('');
                return;
            }
            $badge.text(val + ' — ' + typeText);
        }

        $value.on('input', updatePreview);
        $type.on('change', updatePreview);
        updatePreview();
    })();
</script>