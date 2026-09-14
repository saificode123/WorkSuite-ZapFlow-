<div class="row">
    <div class="col-sm-12">
        <x-form id="save-data-form" method="PUT">
            <div class="add-client bg-white rounded sector-card">
                <div class="sector-card-header p-20 border-bottom-grey">
                    <div class="d-flex align-items-center">
                        <span class="sector-header-icon mr-3">
                            <i class="fa fa-map-marker-alt"></i>
                        </span>
                        <div>
                            <h4 class="mb-0 f-21 font-weight-normal">@lang('app.sector')</h4>
                            <span class="sector-header-sub">@lang('app.updateDetailsBelow')</span>
                        </div>
                    </div>
                </div>

                <div class="row p-20 sector-fields">
                    <div class="col-md-6 mb-3 mb-md-0">
                        <x-forms.text fieldId="name" :fieldLabel="__('app.name')" fieldName="name" fieldRequired="true" fieldValue="{{ $sector->name }}"></x-forms.text>
                    </div>
                    <div class="col-md-6">
                        <x-forms.text fieldId="code" :fieldLabel="__('app.code')" fieldName="code" fieldValue="{{ $sector->code }}"></x-forms.text>
                    </div>
                </div>

                <x-form-actions>
                    <x-forms.button-primary id="save-form" class="mr-3 sector-btn-save" icon="check">@lang('app.save')</x-forms.button-primary>
                    <x-forms.button-cancel :link="route('sectors.index')" class="border-0 sector-btn-cancel">@lang('app.cancel')</x-forms.button-cancel>
                </x-form-actions>
            </div>
        </x-form>
    </div>
</div>

@push('css')
<style>
.sector-card {
    --sc-border:   #e7e9f2;
    --sc-primary:  #4f6ef7;
    --sc-primary-soft: #eef1ff;
    --sc-muted:    #7a8194;
    --sc-text:     #1f2430;
    border: 1px solid var(--sc-border);
    box-shadow: 0 1px 2px rgba(20,24,40,.04), 0 8px 20px -10px rgba(20,24,40,.10);
    overflow: hidden;
    animation: sector-fade-in .25s ease;
}

@keyframes sector-fade-in {
    from { opacity: 0; transform: translateY(4px); }
    to   { opacity: 1; transform: translateY(0); }
}

.sector-card-header {
    background: linear-gradient(180deg, #fbfbfd 0%, #ffffff 100%);
}

.sector-header-icon {
    width: 40px;
    height: 40px;
    flex-shrink: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 10px;
    background: var(--sc-primary-soft);
    color: var(--sc-primary);
    font-size: 16px;
}

.sector-header-sub {
    display: block;
    font-size: 12.5px;
    color: var(--sc-muted);
    margin-top: 2px;
}

.sector-fields .form-group label {
    font-weight: 600;
    font-size: 13px;
    color: var(--sc-text);
}

.sector-fields .form-control {
    border-radius: 8px;
    border-color: var(--sc-border);
    transition: border-color .15s ease, box-shadow .15s ease;
}

.sector-fields .form-control:focus {
    border-color: var(--sc-primary);
    box-shadow: 0 0 0 3px var(--sc-primary-soft);
}

.sector-btn-save {
    border-radius: 8px;
    font-weight: 600;
    box-shadow: 0 4px 10px -4px rgba(79,110,247,.5);
    transition: transform .12s ease, box-shadow .12s ease;
}
.sector-btn-save:hover {
    transform: translateY(-1px);
    box-shadow: 0 6px 14px -4px rgba(79,110,247,.55);
}

.sector-btn-cancel {
    border-radius: 8px;
    color: var(--sc-muted);
    font-weight: 500;
    transition: color .15s ease;
}
.sector-btn-cancel:hover {
    color: var(--sc-text);
}

@media (prefers-reduced-motion: reduce) {
    .sector-card { animation: none; }
    .sector-btn-save { transition: none; }
}
</style>
@endpush

<script>
    $('#save-form').click(function() {
        $.easyAjax({
            url: "{{ route('sectors.update', $sector->id) }}", container: '#save-data-form', type: "POST",
            data: $('#save-data-form').serialize(), disableButton: true, blockUI: true,
            buttonSelector: "#save-form",
            success: function(response) { if (response.status == 'success') window.location.href = response.redirectUrl; }
        });
    });
</script>