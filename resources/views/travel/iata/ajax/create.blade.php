<div class="row">
    <div class="col-sm-12">
        <x-form id="save-data-form">
            <div class="add-client bg-white rounded shadow-sm ct-card">
                <div class="p-20 border-bottom-grey d-flex align-items-center">
                    <div class="ct-header-icon mr-3">
                        <i class="fa fa-plane-departure"></i>
                    </div>
                    <h4 class="mb-0 f-21 font-weight-normal">@lang('app.iata')</h4>
                </div>

                <div class="row p-20">
                    <div class="col-md-6 mb-3 mb-md-0">
                        <x-forms.text fieldId="code" :fieldLabel="__('app.iataCode')" fieldName="code" fieldRequired="true" :fieldPlaceholder="__('placeholders.iataCode')"></x-forms.text>
                        <small id="code-length-hint" class="text-muted f-11 d-block mt-1"></small>
                    </div>
                    <div class="col-md-6">
                        <x-forms.text fieldId="name" :fieldLabel="__('app.name')" fieldName="name" fieldRequired="true" :fieldPlaceholder="__('placeholders.name')"></x-forms.text>
                    </div>

                    <div class="col-md-6 mt-3">
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
    /* Visual only — displayed uppercase, submitted value is unchanged */
    #code {
        text-transform: uppercase;
        letter-spacing: 1px;
    }
</style>

<script>
    // Original save handler — untouched
    $('#save-form').click(function() {
        $.easyAjax({
            url: "{{ route('iata.store') }}", container: '#save-data-form', type: "POST",
            data: $('#save-data-form').serialize(), disableButton: true, blockUI: true,
            buttonSelector: "#save-form",
            success: function(response) { if (response.status == 'success') window.location.href = response.redirectUrl; }
        });
    });

    // Cosmetic-only addition: shows a character count under the code field
    // (IATA codes are conventionally 3 letters). Never trims, blocks, or
    // alters what's typed — purely informational.
    (function() {
        var $code = $('#code');
        var $hint = $('#code-length-hint');

        function updateHint() {
            var len = $.trim($code.val()).length;
            if (len === 0) {
                $hint.text('');
                return;
            }
            $hint.text(len + ' / 3');
        }

        $code.on('input', updateHint);
        updateHint();
    })();
</script>