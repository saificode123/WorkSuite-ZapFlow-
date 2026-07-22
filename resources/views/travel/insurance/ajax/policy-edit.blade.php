<div class="modal-header">
    <h5 class="modal-title">@lang('app.editPolicy')</h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
</div>
<x-form id="policyEditForm" method="POST" class="ajax-form">
    <div class="modal-body">
        <div class="row">
            <div class="col-md-6">
                <x-forms.text fieldId="name" :fieldLabel="__('app.name')" fieldName="name" fieldRequired="true" :fieldValue="$policy->name ?? ''" />
            </div>
            <div class="col-md-6">
                <x-forms.text fieldId="provider" :fieldLabel="__('app.provider')" fieldName="provider" :fieldValue="$policy->provider ?? ''" />
            </div>
            <div class="col-md-6">
                <x-forms.text fieldId="coverage_type" :fieldLabel="__('app.coverageType')" fieldName="coverage_type" :fieldValue="$policy->coverage_type ?? ''" />
            </div>
            <div class="col-md-6">
                <x-forms.number fieldId="premium_pax" :fieldLabel="__('app.premiumPerPax')" fieldName="premium_pax" :fieldValue="$policy->premium_pax ?? 0" />
            </div>
            <div class="col-md-6">
                <x-forms.datepicker fieldId="valid_from" :fieldLabel="__('app.validFrom')" fieldName="valid_from" :fieldValue="$policy->valid_from ? \Carbon\Carbon::parse($policy->valid_from)->format(company()->date_format) : ''" />
            </div>
            <div class="col-md-6">
                <x-forms.datepicker fieldId="valid_to" :fieldLabel="__('app.validTo')" fieldName="valid_to" :fieldValue="$policy->valid_to ? \Carbon\Carbon::parse($policy->valid_to)->format(company()->date_format) : ''" />
            </div>
            <div class="col-md-12">
                <x-forms.checkbox fieldId="is_active" :fieldLabel="__('app.active')" fieldName="is_active" :checked="($policy->is_active ?? true) == true" />
            </div>
            <div class="col-md-12">
                <x-forms.textarea fieldId="description" :fieldLabel="__('app.description')" fieldName="description" :fieldValue="$policy->description ?? ''" />
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <x-forms.button-cancel data-dismiss="modal" class="border-0 mr-3">@lang('app.cancel')</x-forms.button-cancel>
        <x-forms.button-primary id="update-policy-form" icon="check">@lang('app.update')</x-forms.button-primary>
    </div>
</x-form>

<script>
$('#update-policy-form').click(function() {
    $.easyAjax({
        url: "{{ route('insurance-policies.update', $policy->id) }}",
        container: '#policyEditForm',
        type: 'POST',
        data: $('#policyEditForm').serialize() + '&_method=PUT',
        success: function(response) {
            if (response.status === 'success') {
                window.location.reload();
            }
        }
    });
});
</script>
