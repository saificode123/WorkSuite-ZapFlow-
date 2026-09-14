<div class="row">
    <div class="col-sm-12">
        <x-form id="save-data-form" method="PUT">
            <div class="add-client bg-white rounded shadow-sm ct-card">
                <div class="p-20 border-bottom-grey d-flex align-items-center">
                    <div class="ct-header-icon mr-3">
                        <i class="fa fa-tags"></i>
                    </div>
                    <h4 class="mb-0 f-21 font-weight-normal">@lang('app.customerType')</h4>
                </div>

                <div class="row p-20">
                    <div class="col-md-6 mb-3 mb-md-0">
                        <x-forms.text fieldId="name" :fieldLabel="__('app.name')" fieldName="name" fieldRequired="true" fieldValue="{{ $customerType->name }}"></x-forms.text>
                    </div>
                    <div class="col-md-6">
                        <x-forms.text fieldId="description" :fieldLabel="__('app.description')" fieldName="description" fieldValue="{{ $customerType->description }}"></x-forms.text>
                    </div>

                    <div class="col-md-12 mt-3">
                        <x-forms.textarea fieldId="config_json" :fieldLabel="__('app.configJson')" fieldName="config_json" :fieldValue="$customerType->config_json ? json_encode($customerType->config_json) : ''"></x-forms.textarea>

                        <div class="d-flex justify-content-between align-items-center mt-1">
                            <small class="text-muted f-12">@lang('app.configJson') @lang('app.mustBeValidJson')</small>
                            <span id="json-validity-badge" class="badge badge-secondary f-11"></span>
                        </div>
                    </div>
                </div>

                <x-form-actions>
                    <x-forms.button-primary id="save-form" class="mr-3" icon="check">@lang('app.save')</x-forms.button-primary>
                    <x-forms.button-cancel :link="route('customer-types.index')" class="border-0">@lang('app.cancel')</x-forms.button-cancel>
                </x-form-actions>
            </div>
        </x-form>
    </div>
</div>

<style>
    .ct-card {
        animation: ctFadeIn .25s ease-out;
    }
    @keyframes ctFadeIn {
        from { opacity: 0; transform: translateY(6px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    .ct-header-icon {
        width: 36px;
        height: 36px;
        border-radius: 8px;
        background: rgba(0, 123, 255, .08);
        color: #007bff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 15px;
    }
    #config_json {
        font-family: 'SFMono-Regular', Consolas, 'Liberation Mono', Menlo, monospace;
        font-size: 13px;
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
            url: "{{ route('customer-types.update', $customerType->id) }}", container: '#save-data-form', type: "POST",
            data: $('#save-data-form').serialize(), disableButton: true, blockUI: true,
            buttonSelector: "#save-form",
            success: function(response) { if (response.status == 'success') window.location.href = response.redirectUrl; }
        });
    });

    // Cosmetic-only addition: shows whether config_json is valid JSON as the user types.
    // It never blocks or alters submission — purely a visual hint.
    (function() {
        var $textarea = $('#config_json');
        var $badge = $('#json-validity-badge');

        function updateBadge() {
            var val = $.trim($textarea.val());
            if (val === '') {
                $badge.text('').removeClass('badge-success badge-danger').addClass('badge-secondary');
                return;
            }
            try {
                JSON.parse(val);
                $badge.text('@lang('app.validJson')').removeClass('badge-secondary badge-danger').addClass('badge-success');
            } catch (e) {
                $badge.text('@lang('app.invalidJson')').removeClass('badge-secondary badge-success').addClass('badge-danger');
            }
        }

        $textarea.on('input', updateBadge);
        updateBadge();
    })();
</script>