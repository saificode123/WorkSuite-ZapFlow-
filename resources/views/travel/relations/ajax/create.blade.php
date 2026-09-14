<div class="row">
    <div class="col-sm-12">
        <x-form id="save-data-form">
            <div class="add-client bg-white rounded">
                <div class="d-flex align-items-center p-20 border-bottom-grey">
                    <div class="icon-circle bg-light-primary text-primary mr-3">
                        <i class="fa fa-sitemap" aria-hidden="true"></i>
                    </div>
                    <h4 class="mb-0 f-21 font-weight-normal">@lang('app.relation')</h4>
                </div>

                <div class="row p-20">
                    <div class="col-md-8 col-lg-6">
                        <x-forms.text fieldId="name" :fieldLabel="__('app.name')" fieldName="name" fieldRequired="true"></x-forms.text>
                        <small class="text-muted">@lang('modules.relation.nameHelp', ['default' => 'Enter a name to identify this relation, e.g. Father, Spouse, Friend.'])</small>
                    </div>
                </div>

                <x-form-actions>
                    <x-forms.button-primary id="save-form" class="mr-3" icon="check">@lang('app.save')</x-forms.button-primary>
                    <x-forms.button-cancel :link="route('relations.index')" class="border-0">@lang('app.cancel')</x-forms.button-cancel>
                </x-form-actions>
            </div>
        </x-form>
    </div>
</div>

<style>
    .icon-circle {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        flex-shrink: 0;
    }
    .bg-light-primary { background-color: rgba(13, 110, 253, 0.1); }
</style>

<script>
    $('#save-form').click(function() {
        $.easyAjax({
            url: "{{ route('relations.store') }}", container: '#save-data-form', type: "POST",
            data: $('#save-data-form').serialize(), disableButton: true, blockUI: true,
            buttonSelector: "#save-form",
            success: function(response) { if (response.status == 'success') window.location.href = response.redirectUrl; }
        });
    });
</script>