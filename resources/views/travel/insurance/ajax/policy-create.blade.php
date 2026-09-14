<div class="modal-header">
    <div class="d-flex align-items-center">
        <div class="ct-header-icon mr-3">
            <i class="fa fa-shield-alt"></i>
        </div>
        <h5 class="modal-title mb-0">@lang('app.addPolicy')</h5>
    </div>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
</div>
<x-form id="policyForm" method="POST" class="ajax-form">
    <div class="modal-body">
        <div class="row">
            <div class="col-md-6 mb-3 mb-md-0">
                <x-forms.text fieldId="name" :fieldLabel="__('app.name')" fieldName="name" fieldRequired="true" />
            </div>
            <div class="col-md-6">
                <x-forms.text fieldId="provider" :fieldLabel="__('app.provider')" fieldName="provider" />
            </div>

            <div class="col-md-6 mt-3">
                <x-forms.text fieldId="coverage_type" :fieldLabel="__('app.coverageType')" fieldName="coverage_type" />
            </div>
            <div class="col-md-6 mt-3">
                <x-forms.number fieldId="premium_pax" :fieldLabel="__('app.premiumPerPax')" fieldName="premium_pax" />
            </div>

            <div class="col-md-6 mt-3">
                <x-forms.datepicker fieldId="valid_from" :fieldLabel="__('app.validFrom')" fieldName="valid_from" />
            </div>
            <div class="col-md-6 mt-3">
                <x-forms.datepicker fieldId="valid_to" :fieldLabel="__('app.validTo')" fieldName="valid_to" />
                <small id="validity-duration-hint" class="text-muted f-11 d-block mt-1"></small>
            </div>

            <div class="col-md-12 mt-3 d-flex align-items-center">
                <x-forms.checkbox fieldId="is_active" :fieldLabel="__('app.active')" fieldName="is_active" :checked="true" />
                <span id="policy-status-badge" class="badge f-11 ml-2"></span>
            </div>
            <div class="col-md-12 mt-3">
                <x-forms.textarea fieldId="description" :fieldLabel="__('app.description')" fieldName="description" />
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <x-forms.button-cancel data-dismiss="modal" class="border-0 mr-3">@lang('app.cancel')</x-forms.button-cancel>
        <x-forms.button-primary id="save-policy-form" icon="check">@lang('app.save')</x-forms.button-primary>
    </div>
</x-form>

<style>
    .ct-header-icon {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        background: rgba(0, 123, 255, .08);
        color: #007bff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 13px;
        flex-shrink: 0;
    }
    #save-policy-form {
        transition: transform .12s ease, box-shadow .12s ease;
    }
    #save-policy-form:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 10px rgba(0, 0, 0, .12);
    }
</style>

<script>
    // Original save handler — untouched
    $('#save-policy-form').click(function() {
        $.easyAjax({
            url: "{{ route('insurance-policies.store') }}",
            container: '#policyForm',
            type: 'POST',
            data: $('#policyForm').serialize(),
            success: function(response) {
                if (response.status === 'success') {
                    window.location.reload();
                }
            }
        });
    });

    // Cosmetic-only additions below. Neither reads nor writes anything that
    // gets submitted — both are pure visual reflections of existing state.
    (function() {
        var $from = $('#valid_from');
        var $to = $('#valid_to');
        var $hint = $('#validity-duration-hint');

        function updateDurationHint() {
            var fromDate = new Date($from.val());
            var toDate = new Date($to.val());
            if (isNaN(fromDate) || isNaN(toDate)) {
                $hint.text('');
                return;
            }
            var days = Math.round((toDate - fromDate) / 86400000);
            $hint.text(days >= 0 ? days + ' ' + '@lang('app.days')' : '');
        }

        $from.add($to).on('change', updateDurationHint);
        updateDurationHint();
    })();

    (function() {
        var $checkbox = $('#is_active');
        var $badge = $('#policy-status-badge');

        function updateStatusBadge() {
            var active = $checkbox.is(':checked');
            $badge
                .text(active ? '@lang('app.active')' : '@lang('app.inactive')')
                .removeClass('badge-success badge-secondary')
                .addClass(active ? 'badge-success' : 'badge-secondary');
        }

        $checkbox.on('change', updateStatusBadge);
        updateStatusBadge();
    })();
</script>