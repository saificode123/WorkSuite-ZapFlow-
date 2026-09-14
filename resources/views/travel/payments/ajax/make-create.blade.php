<div class="modal-header">
    <div class="d-flex align-items-center">
        <div class="icon-circle bg-light-primary text-primary mr-3">
            <i class="fa fa-money-bill-wave" aria-hidden="true"></i>
        </div>
        <h5 class="modal-title mb-0">@lang('modules.travelPayments.makePayment')</h5>
    </div>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
</div>

<x-form id="makePaymentForm" method="POST" class="ajax-form">
    <div class="modal-body">

        {{-- Accounts --}}
        <h6 class="text-uppercase text-muted f-13 font-weight-bold mb-3">
            <i class="fa fa-exchange-alt mr-1"></i> @lang('modules.travelPayments.transferDetails', ['default' => 'Transfer Details'])
        </h6>
        <div class="row">
            <div class="col-md-6">
                <x-forms.select fieldId="debit_account_id" :fieldLabel="__('modules.travelPayments.payFrom')" fieldName="debit_account_id" search="true">
                    <option value="">--</option>
                    @foreach($accounts as $account)
                        <option value="{{ $account->id }}">{{ $account->name }}</option>
                    @endforeach
                </x-forms.select>
            </div>
            <div class="col-md-6">
                <x-forms.select fieldId="credit_account_id" :fieldLabel="__('modules.travelPayments.payTo')" fieldName="credit_account_id" search="true">
                    <option value="">-- @lang('app.selectAccount') --</option>
                    @foreach($accounts as $account)
                        <option value="{{ $account->id }}">{{ $account->name }}</option>
                    @endforeach
                </x-forms.select>
            </div>
        </div>

        <hr class="my-4">

        {{-- Payment details --}}
        <h6 class="text-uppercase text-muted f-13 font-weight-bold mb-3">
            <i class="fa fa-file-invoice-dollar mr-1"></i> @lang('app.paymentDetails', ['default' => 'Payment Details'])
        </h6>
        <div class="row">
            <div class="col-md-6">
                <x-forms.number fieldId="amount" :fieldLabel="__('app.amount')" fieldName="amount" fieldRequired="true" />
            </div>
            <div class="col-md-6">
                <x-forms.datepicker fieldId="payment_date" :fieldLabel="__('app.date')" fieldName="payment_date"
                    :fieldValue="\Carbon\Carbon::now()->format(company()->date_format)" fieldRequired="true" />
            </div>
            <div class="col-md-4">
                <x-forms.select fieldId="payment_method" :fieldLabel="__('app.paymentMethod')" fieldName="payment_method" fieldRequired="true">
                    <option value="bank_transfer">@lang('app.bankTransfer')</option>
                    <option value="cash">@lang('app.cash')</option>
                    <option value="cheque">@lang('app.cheque')</option>
                    <option value="online">@lang('app.online')</option>
                </x-forms.select>
            </div>
            <div class="col-md-4">
                <x-forms.text fieldId="reference_no" :fieldLabel="__('app.reference')" fieldName="reference_no" />
            </div>
            <div class="col-md-4" id="cheque-no-wrapper">
                <x-forms.text fieldId="cheque_no" :fieldLabel="__('app.chequeNo')" fieldName="cheque_no" />
            </div>
            <div class="col-md-12">
                <x-forms.textarea fieldId="narration" :fieldLabel="__('app.narration')" fieldName="narration" />
            </div>
        </div>
    </div>

    <div class="modal-footer">
        <x-forms.button-cancel data-dismiss="modal" class="border-0 mr-3">@lang('app.cancel')</x-forms.button-cancel>
        <x-forms.button-primary id="save-make-form" icon="check">@lang('app.makePayment')</x-forms.button-primary>
    </div>
</x-form>

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
$('#save-make-form').click(function() {
    $.easyAjax({
        url: "{{ route('travel-payments.make.store') }}",
        container: '#makePaymentForm',
        type: 'POST',
        data: $('#makePaymentForm').serialize(),
        success: function(response) {
            if (response.status === 'success') {
                window.location.href = response.redirectUrl || '{{ route('travel-payments.make.index') }}';
            }
        }
    });
});

// Purely cosmetic: cheque_no field is still always in the form and still submits normally.
// This only toggles its visibility so it's not confusingly shown for non-cheque payments.
function toggleChequeField() {
    if ($('#payment_method').val() === 'cheque') {
        $('#cheque-no-wrapper').show();
    } else {
        $('#cheque-no-wrapper').show(); // change to .hide() only if you want the visual toggle
    }
}
$('#payment_method').on('change', toggleChequeField);
toggleChequeField();
</script>