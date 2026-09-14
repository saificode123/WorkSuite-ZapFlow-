<div class="row">
    <div class="col-sm-12">
        <x-form id="save-data-form">
            <div class="add-client bg-white rounded shadow-sm ct-card">
                <div class="p-20 border-bottom-grey d-flex align-items-center">
                    <div class="ct-header-icon mr-3">
                        <i class="fa fa-hotel"></i>
                    </div>
                    <h4 class="mb-0 f-21 font-weight-normal">@lang('app.hotel')</h4>
                </div>

                <div class="row p-20">
                    <div class="col-md-6 mb-3 mb-md-0">
                        <x-forms.text fieldId="name" :fieldLabel="__('app.name')" fieldName="name" fieldRequired="true"></x-forms.text>
                    </div>
                    <div class="col-md-6">
                        <x-forms.number fieldId="stars" :fieldLabel="__('modules.hotel.starRating')" fieldName="stars" min="1" max="5"></x-forms.number>
                        <div id="stars-preview" class="mt-1"></div>
                    </div>

                    <div class="col-md-6 mt-3">
                        <x-forms.text fieldId="city" :fieldLabel="__('app.city')" fieldName="city"></x-forms.text>
                    </div>
                    <div class="col-md-6 mt-3">
                        <x-forms.text fieldId="country" :fieldLabel="__('app.country')" fieldName="country"></x-forms.text>
                    </div>

                    <div class="col-md-6 mt-3">
                        <x-forms.email fieldId="email" :fieldLabel="__('app.email')" :fieldPlaceholder="__('app.email')" fieldName="email"></x-forms.email>
                    </div>
                    <div class="col-md-6 mt-3">
                        <x-forms.text fieldId="phone" :fieldLabel="__('app.phone')" fieldName="phone"></x-forms.text>
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
    #stars-preview {
        font-size: 13px;
        letter-spacing: 1px;
        color: #e0a800;
        min-height: 16px;
    }
    #stars-preview .fa-star.empty {
        color: #dee2e6;
    }
</style>

<script>
    // Original save handler — untouched
    $('#save-form').click(function() {
        $.easyAjax({
            url: "{{ route('hotels.store') }}", container: '#save-data-form', type: "POST",
            data: $('#save-data-form').serialize(), disableButton: true, blockUI: true,
            buttonSelector: "#save-form",
            success: function(response) { if (response.status == 'success') window.location.href = response.redirectUrl; }
        });
    });

    // Cosmetic-only addition: renders filled/empty stars under the numeric
    // "stars" field as you type. Purely visual — reads the field, never
    // writes to it, and doesn't clamp or validate the typed value.
    (function() {
        var $stars = $('#stars');
        var $preview = $('#stars-preview');

        function updateStarsPreview() {
            var val = parseInt($stars.val(), 10);
            if (isNaN(val)) {
                $preview.html('');
                return;
            }
            var html = '';
            for (var i = 1; i <= 5; i++) {
                html += '<i class="fa fa-star' + (i <= val ? '' : ' empty') + '"></i> ';
            }
            $preview.html(html);
        }

        $stars.on('input', updateStarsPreview);
        updateStarsPreview();
    })();
</script>