@extends('layouts.app')

@section('content')
<div class="content-wrapper">
    <div class="card border-0 shadow-sm rounded">
        <div class="card-header bg-white border-bottom p-3">
            <h5 class="mb-0 font-weight-bold"><i class="fa fa-file-invoice mr-2 text-primary"></i>Create Other Service Invoice</h5>
            <small class="text-muted">A balanced journal voucher will automatically be posted to the general ledger upon saving.</small>
        </div>
        <div class="card-body p-4">
            <form id="save-osi-form" method="POST" action="{{ route('other-service-invoices.store') }}">
                @csrf
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label font-weight-bold">Invoice Number <span class="text-danger">*</span></label>
                        <input type="text" name="invoice_number" class="form-control" value="{{ $nextInvoiceNumber }}" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label font-weight-bold">Issue Date <span class="text-danger">*</span></label>
                        <input type="date" name="issue_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label font-weight-bold">Due Date</label>
                        <input type="date" name="due_date" class="form-control" value="{{ date('Y-m-d', strtotime('+30 days')) }}">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label font-weight-bold">Customer / Client</label>
                        <select name="customer_id" class="form-control select-picker" data-live-search="true">
                            <option value="">Walk-in Customer</option>
                            @foreach($customers as $c)
                                <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->email }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label font-weight-bold">Service Type <span class="text-danger">*</span></label>
                        <select name="service_type" class="form-control" required>
                            <option value="visa_service">Visa Processing / Medical</option>
                            <option value="transport_charter">Private Transport / Charter</option>
                            <option value="hotel_extra">Hotel Extra / Upgrade</option>
                            <option value="ziarat">Ziarat Excursion</option>
                            <option value="laundry_catering">Catering / Laundry</option>
                            <option value="other" selected>Other Custom Service</option>
                        </select>
                    </div>

                    <div class="col-md-12 mb-3">
                        <label class="form-label font-weight-bold">Service Description</label>
                        <textarea name="description" class="form-control" rows="2" placeholder="Details of the specific service provided..."></textarea>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label font-weight-bold">Base Amount <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" min="0.01" name="amount" id="amount" class="form-control font-weight-bold" required oninput="calcTotal()">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label font-weight-bold">Tax Amount</label>
                        <input type="number" step="0.01" min="0" name="tax_amount" id="tax_amount" class="form-control" value="0.00" oninput="calcTotal()">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label font-weight-bold">Total Amount <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" min="0.01" name="total_amount" id="total_amount" class="form-control font-weight-bold bg-light" readonly required>
                    </div>

                    <div class="col-md-12 mb-2">
                        <div class="alert alert-info py-2">
                            <i class="fa fa-info-circle mr-1"></i><strong>Double-Entry GL Posting:</strong> The receivable debit account is charged for the total invoice amount, and the revenue credit account is credited.
                        </div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label font-weight-bold">Debit Account (Receivable) <span class="text-danger">*</span></label>
                        <select name="debit_account_id" class="form-control" required>
                            @foreach($accounts as $acc)
                                <option value="{{ $acc->id }}" {{ ($defaultDebitAccount && $acc->id == $defaultDebitAccount->id) ? 'selected' : '' }}>
                                    [{{ $acc->code }}] {{ $acc->name }} ({{ strtoupper($acc->type) }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label font-weight-bold">Credit Account (Revenue / Income) <span class="text-danger">*</span></label>
                        <select name="credit_account_id" class="form-control" required>
                            @foreach($accounts as $acc)
                                <option value="{{ $acc->id }}" {{ ($defaultCreditAccount && $acc->id == $defaultCreditAccount->id) ? 'selected' : '' }}>
                                    [{{ $acc->code }}] {{ $acc->name }} ({{ strtoupper($acc->type) }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-12 mb-3">
                        <label class="form-label font-weight-bold">Internal Notes</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="Optional notes for accounting / audit records"></textarea>
                    </div>
                </div>

                <div class="border-top pt-3 text-right">
                    <a href="{{ route('other-service-invoices.index') }}" class="btn btn-secondary mr-2">Cancel</a>
                    <button type="submit" id="save-osi-btn" class="btn btn-primary"><i class="fa fa-check mr-1"></i>Save &amp; Post Voucher</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function calcTotal() {
    var amt = parseFloat($('#amount').val()) || 0;
    var tax = parseFloat($('#tax_amount').val()) || 0;
    $('#total_amount').val((amt + tax).toFixed(2));
}

$('#save-osi-btn').click(function(e) {
    e.preventDefault();
    $.easyAjax({
        url: "{{ route('other-service-invoices.store') }}",
        container: '#save-osi-form',
        type: "POST",
        data: $('#save-osi-form').serialize(),
        disableButton: true,
        blockUI: true,
        buttonSelector: "#save-osi-btn",
        success: function(response) {
            if (response.status == 'success') {
                window.location.href = response.redirectUrl;
            }
        }
    });
});
</script>
@endpush
@endsection
