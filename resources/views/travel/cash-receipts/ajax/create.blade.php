<div class="modal-header">
    <h5 class="modal-title"><i class="fa fa-receipt mr-2 text-primary"></i>@lang('app.addCashReceipt')</h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
</div>
<x-form id="cashReceiptForm" method="POST" class="ajax-form">
    <div class="modal-body">

        {{-- Receipt Details --}}
        <h6 class="text-muted text-uppercase f-13 font-weight-bold mb-3">
            <i class="fa fa-info-circle mr-1"></i>Receipt Details
        </h6>
        <div class="row mb-2">
            <div class="col-md-6">
                <x-forms.select fieldId="account_id" :fieldLabel="__('app.cashAccount')" fieldName="account_id" fieldRequired="true" search="true">
                    <option value="">--</option>
                    @foreach($accounts as $account)
                        <option value="{{ $account->id }}">{{ $account->name }}</option>
                    @endforeach
                </x-forms.select>
            </div>
            <div class="col-md-6">
                <x-forms.number fieldId="amount" :fieldLabel="__('app.amount')" fieldName="amount" fieldRequired="true" />
            </div>
            <div class="col-md-6">
                <x-forms.datepicker fieldId="date" :fieldLabel="__('app.date')" fieldName="date"
                    :fieldValue="\Carbon\Carbon::now()->format(company()->date_format)" fieldRequired="true" />
            </div>
        </div>

        <hr class="my-4">

        {{-- Reference Details --}}
        <h6 class="text-muted text-uppercase f-13 font-weight-bold mb-3">
            <i class="fa fa-align-left mr-1"></i>Reference Details
        </h6>
        <div class="row">
            <div class="col-md-6">
                <x-forms.text fieldId="received_from" :fieldLabel="__('app.receivedFrom')" fieldName="received_from" fieldRequired="true" />
            </div>
            <div class="col-md-6">
                <x-forms.text fieldId="reference_no" :fieldLabel="__('app.reference')" fieldName="reference_no" />
            </div>
            <div class="col-md-12">
                <x-forms.textarea fieldId="narration" :fieldLabel="__('app.narration')" fieldName="narration" />
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <x-forms.button-cancel data-dismiss="modal" class="border-0 mr-3">@lang('app.cancel')</x-forms.button-cancel>
        <x-forms.button-primary id="save-cash-receipt" icon="check">@lang('app.save')</x-forms.button-primary>
    </div>
</x-form>

<script>
$('#save-cash-receipt').click(function() {
    $.easyAjax({
        url: "{{ route('cash-receipts.store') }}",
        container: '#cashReceiptForm',
        type: 'POST',
        data: $('#cashReceiptForm').serialize(),
        success: function(response) {
            if (response.status === 'success') {
                window.location.href = response.redirectUrl || '{{ route('cash-receipts.index') }}';
            }
        }
    });
});
</script>