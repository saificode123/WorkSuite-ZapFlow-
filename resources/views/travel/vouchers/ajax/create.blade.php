<div class="row">
    <div class="col-sm-12">
        <x-form id="save-data-form">
            <div class="add-client bg-white rounded shadow-sm ct-card">
                <div class="p-20 border-bottom-grey d-flex align-items-center">
                    <div class="ct-header-icon mr-3">
                        <i class="fa fa-receipt"></i>
                    </div>
                    <h4 class="mb-0 f-21 font-weight-normal">@lang('app.voucher')</h4>
                </div>

                <div class="row p-20">
                    <div class="col-md-6 mb-3 mb-md-0">
                        <x-forms.text fieldId="voucher_number" :fieldLabel="__('modules.voucher.voucherNumber')" fieldName="voucher_number" fieldRequired="true"></x-forms.text>
                    </div>
                    <div class="col-md-6">
                        <x-forms.datepicker fieldId="date" :fieldLabel="__('app.date')" fieldName="date"></x-forms.datepicker>
                    </div>

                    <div class="col-md-6 mt-3">
                        <x-forms.text fieldId="type" :fieldLabel="__('app.type')" fieldName="type"></x-forms.text>
                    </div>
                    <div class="col-md-6 mt-3">
                        <x-forms.number fieldId="total_amount" :fieldLabel="__('modules.voucher.totalAmount')" fieldName="total_amount"></x-forms.number>
                    </div>

                    <div class="col-md-6 mt-3">
                        <x-forms.select fieldId="status" :fieldLabel="__('app.status')" fieldName="status">
                            <option value="draft">@lang('app.draft')</option>
                            <option value="issued">@lang('app.issued')</option>
                            <option value="cancelled">@lang('app.cancelled')</option>
                        </x-forms.select>
                        <span id="voucher-status-badge" class="badge f-11 mt-1"></span>
                    </div>
                </div>
                <x-form-actions>
                    <x-forms.button-primary id="save-form" class="mr-3" icon="check">@lang('app.save')</x-forms.button-primary>
                    <x-forms.button-cancel :link="route('vouchers.index')" class="border-0">@lang('app.cancel')</x-forms.button-cancel>
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
            url: "{{ route('vouchers.store') }}", container: '#save-data-form', type: "POST",
            data: $('#save-data-form').serialize(), disableButton: true, blockUI: true,
            buttonSelector: "#save-form",
            success: function(response) { if (response.status == 'success') window.location.href = response.redirectUrl; }
        });
    });

    // Cosmetic-only addition: colors a badge to match the selected status.
    // Purely a visual echo of the dropdown's own current value.
    (function() {
        var statusColor = { draft: 'badge-secondary', issued: 'badge-success', cancelled: 'badge-danger' };
        var $status = $('#status');
        var $badge = $('#voucher-status-badge');

        function updateStatusBadge() {
            var val = $status.val();
            var cls = statusColor[val] || 'badge-secondary';
            $badge
                .text($status.find('option:selected').text())
                .removeClass('badge-secondary badge-success badge-danger')
                .addClass(cls);
        }

        $status.on('change', updateStatusBadge);
        updateStatusBadge();
    })();
</script>