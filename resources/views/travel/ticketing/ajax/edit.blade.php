<div class="row">
    <div class="col-sm-12">
        <x-form id="save-data-form" method="PUT">
            <div class="add-client bg-white rounded">
                <h4 class="mb-0 p-20 f-21 font-weight-normal border-bottom-grey">@lang('app.ticketInvoice')</h4>
                <div class="row p-20">
                    <div class="col-md-6">
                        <x-forms.text fieldId="invoice_number" :fieldLabel="__('modules.ticketing.invoiceNumber')" fieldName="invoice_number" fieldRequired="true" fieldValue="{{ $ticketInvoice->invoice_number }}"></x-forms.text>
                    </div>
                    <div class="col-md-6">
                        <x-forms.datepicker fieldId="date" :fieldLabel="__('app.date')" fieldName="date" fieldValue="{{ $ticketInvoice->date ? $ticketInvoice->date->format(company()->date_format) : '' }}"></x-forms.datepicker>
                    </div>
                    <div class="col-md-6">
                        <x-forms.number fieldId="ticket_count" :fieldLabel="__('modules.ticketing.ticketCount')" fieldName="ticket_count" fieldValue="{{ $ticketInvoice->ticket_count }}"></x-forms.number>
                    </div>
                    <div class="col-md-6">
                        <x-forms.number fieldId="total_amount" :fieldLabel="__('modules.ticketing.totalAmount')" fieldName="total_amount" fieldValue="{{ $ticketInvoice->total_amount }}"></x-forms.number>
                    </div>
                    <div class="col-md-6">
                        <x-forms.select fieldId="status" :fieldLabel="__('app.status')" fieldName="status">
                            <option value="pending" {{ $ticketInvoice->status == 'pending' ? 'selected' : '' }}>@lang('app.pending')</option>
                            <option value="paid" {{ $ticketInvoice->status == 'paid' ? 'selected' : '' }}>@lang('app.paid')</option>
                            <option value="cancelled" {{ $ticketInvoice->status == 'cancelled' ? 'selected' : '' }}>@lang('app.cancelled')</option>
                        </x-forms.select>
                    </div>
                </div>
                <x-form-actions>
                    <x-forms.button-primary id="save-form" class="mr-3" icon="check">@lang('app.save')</x-forms.button-primary>
                    <x-forms.button-cancel :link="route('ticketing.index')" class="border-0">@lang('app.cancel')</x-forms.button-cancel>
                </x-form-actions>
            </div>
        </x-form>
    </div>
</div>
<script>
    $('#save-form').click(function() {
        $.easyAjax({
            url: "{{ route('ticketing.update', $ticketInvoice->id) }}", container: '#save-data-form', type: "POST",
            data: $('#save-data-form').serialize(), disableButton: true, blockUI: true,
            buttonSelector: "#save-form",
            success: function(response) { if (response.status == 'success') window.location.href = response.redirectUrl; }
        });
    });
</script>
