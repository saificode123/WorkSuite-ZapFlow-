<div class="modal-header">
    <h5 class="modal-title">@lang('modules.travelPayments.receivePayment')</h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
</div>
<x-form id="receivePaymentForm" method="POST" class="ajax-form">
    <div class="modal-body">
        <div class="row">
            <div class="col-md-6">
                <x-forms.select fieldId="party_user_id" :fieldLabel="__('modules.travelPayments.receivedFrom')" fieldName="party_user_id" search="true">
                    <option value="">--</option>
                    @foreach($clients as $client)
                        <option value="{{ $client->id }}">{{ $client->name }} ({{ $client->email }})</option>
                    @endforeach
                </x-forms.select>
            </div>
            <div class="col-md-6">
                <x-forms.select fieldId="debit_account_id" :fieldLabel="__('modules.travelPayments.depositTo')" fieldName="debit_account_id" search="true">
                    <option value="">--</option>
                    @foreach($accounts as $account)
                        <option value="{{ $account->id }}">{{ $account->name }}</option>
                    @endforeach
                </x-forms.select>
            </div>
            <div class="col-md-6">
                <x-forms.select fieldId="credit_account_id" :fieldLabel="__('modules.travelPayments.creditAccount')" fieldName="credit_account_id" search="true">
                    <option value="">-- @lang('app.selectAccount') --</option>
                    @foreach($accounts as $account)
                        <option value="{{ $account->id }}">{{ $account->name }}</option>
                    @endforeach
                </x-forms.select>
            </div>
            <div class="col-md-6">
                <x-forms.number fieldId="amount" :fieldLabel="__('app.amount')" fieldName="amount" fieldRequired="true" />
            </div>
            <div class="col-md-4">
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
            <div class="col-md-6">
                <x-forms.text fieldId="cheque_no" :fieldLabel="__('app.chequeNo')" fieldName="cheque_no" />
            </div>
            <div class="col-md-6">
                <x-forms.text fieldId="exchange_rate" :fieldLabel="__('app.exchangeRate')" fieldName="exchange_rate" fieldValue="1" />
            </div>
            @if(request()->booking_group_id)
                <input type="hidden" name="booking_group_id" value="{{ request()->booking_group_id }}">
            @endif
            <div class="col-md-12">
                <x-forms.textarea fieldId="narration" :fieldLabel="__('app.narration')" fieldName="narration" />
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <x-forms.button-cancel data-dismiss="modal" class="border-0 mr-3">@lang('app.cancel')</x-forms.button-cancel>
        <x-forms.button-primary id="save-receive-form" icon="check">@lang('app.receivePayment')</x-forms.button-primary>
    </div>
</x-form>

<script>
$('#save-receive-form').click(function() {
    $.easyAjax({
        url: "{{ route('travel-payments.receive.store') }}",
        container: '#receivePaymentForm',
        type: 'POST',
        data: $('#receivePaymentForm').serialize(),
        success: function(response) {
            if (response.status === 'success') {
                window.location.href = response.redirectUrl || '{{ route('travel-payments.receive.index') }}';
            }
        }
    });
});
</script>
