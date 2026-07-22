@php
    $edit = isset($voucher);
@endphp

<x-form method="{{ $edit ? 'PUT' : 'POST' }}" action="{{ $edit ? route('journal-vouchers.update', $voucher->id) : route('journal-vouchers.store') }}">
    <div class="row">
        <div class="col-md-3">
            <x-forms.text fieldId="voucher_number" :fieldLabel="__('modules.accounts.voucherNumber')" fieldName="voucher_number"
                :fieldValue="$edit ? $voucher->voucher_number : ($voucherNumber ?? '')" :fieldRequired="true" :readonly="$edit" />
        </div>
        <div class="col-md-3">
            <x-forms.datepicker fieldId="date" :fieldLabel="__('app.date')" fieldName="date"
                :fieldValue="$edit ? $voucher->date->format(company()->date_format) : now()->format(company()->date_format)" :fieldRequired="true" />
        </div>
        <div class="col-md-3">
            <x-forms.select fieldId="financial_year_id" :fieldLabel="__('modules.accounts.financialYear')" fieldName="financial_year_id" :fieldRequired="true">
                @foreach ($financialYears as $fy)
                    <option value="{{ $fy->id }}" @if($edit && $voucher->financial_year_id == $fy->id) selected @endif>{{ $fy->name }}</option>
                @endforeach
            </x-forms.select>
        </div>
        <div class="col-md-3">
            <x-forms.textarea fieldId="narration" :fieldLabel="__('modules.accounts.narration')" fieldName="narration"
                :fieldValue="$edit ? $voucher->narration : ''" />
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-sm-12">
            <div class="table-responsive">
                <table class="table table-bordered" id="voucher-lines-table">
                    <thead>
                        <tr>
                            <th>@lang('modules.accounts.account')</th>
                            <th>@lang('modules.accounts.description')</th>
                            <th class="text-right">@lang('modules.accounts.debit')</th>
                            <th class="text-right">@lang('modules.accounts.credit')</th>
                            <th>@lang('app.action')</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if($edit && $voucher->lines->count() > 0)
                            @foreach($voucher->lines as $line)
                                <tr>
                                    <td>
                                        <select name="lines[{{ $loop->index }}][account_id]" class="form-control select-picker" required>
                                            <option value="">@lang('app.select')</option>
                                            @foreach($accounts as $account)
                                                <option value="{{ $account->id }}" @if($line->account_id == $account->id) selected @endif>{{ $account->name }} ({{ $account->code }})</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td><input type="text" name="lines[{{ $loop->index }}][description]" class="form-control" value="{{ $line->description }}"></td>
                                    <td><input type="number" step="0.01" min="0" name="lines[{{ $loop->index }}][debit]" class="form-control text-right" value="{{ $line->debit }}" onchange="updateTotals()"></td>
                                    <td><input type="number" step="0.01" min="0" name="lines[{{ $loop->index }}][credit]" class="form-control text-right" value="{{ $line->credit }}" onchange="updateTotals()"></td>
                                    <td><button type="button" class="btn btn-danger btn-sm" onclick="removeLine(this)"><i class="fa fa-times"></i></button></td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td>
                                    <select name="lines[0][account_id]" class="form-control select-picker" required>
                                        <option value="">@lang('app.select')</option>
                                        @foreach($accounts as $account)
                                            <option value="{{ $account->id }}">{{ $account->name }} ({{ $account->code }})</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td><input type="text" name="lines[0][description]" class="form-control"></td>
                                <td><input type="number" step="0.01" min="0" name="lines[0][debit]" class="form-control text-right" value="0" onchange="updateTotals()"></td>
                                <td><input type="number" step="0.01" min="0" name="lines[0][credit]" class="form-control text-right" value="0" onchange="updateTotals()"></td>
                                <td><button type="button" class="btn btn-danger btn-sm" onclick="removeLine(this)"><i class="fa fa-times"></i></button></td>
                            </tr>
                            <tr>
                                <td>
                                    <select name="lines[1][account_id]" class="form-control select-picker" required>
                                        <option value="">@lang('app.select')</option>
                                        @foreach($accounts as $account)
                                            <option value="{{ $account->id }}">{{ $account->name }} ({{ $account->code }})</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td><input type="text" name="lines[1][description]" class="form-control"></td>
                                <td><input type="number" step="0.01" min="0" name="lines[1][debit]" class="form-control text-right" value="0" onchange="updateTotals()"></td>
                                <td><input type="number" step="0.01" min="0" name="lines[1][credit]" class="form-control text-right" value="0" onchange="updateTotals()"></td>
                                <td><button type="button" class="btn btn-danger btn-sm" onclick="removeLine(this)"><i class="fa fa-times"></i></button></td>
                            </tr>
                        @endif
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="2">@lang('app.total')</th>
                            <th class="text-right" id="total-debit">0.00</th>
                            <th class="text-right" id="total-credit">0.00</th>
                            <th><button type="button" class="btn btn-success btn-sm" onclick="addLine()"><i class="fa fa-plus"></i></button></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <div class="w-100 border-top-grey mt-3 pt-3">
        <x-forms.button-primary icon="check">@lang('app.save')</x-forms.button-primary>
    </div>
</x-form>

@push('scripts')
<script>
    var lineIndex = {{ ($edit && $voucher->lines->count() > 0) ? $voucher->lines->count() : 2 }};

    function addLine() {
        var html = '<tr>';
        html += '<td><select name="lines[' + lineIndex + '][account_id]" class="form-control select-picker" required>';
        html += '<option value="">@lang('app.select')</option>';
        @foreach($accounts as $account)
            html += '<option value="{{ $account->id }}">{{ $account->name }} ({{ $account->code }})</option>';
        @endforeach
        html += '</select></td>';
        html += '<td><input type="text" name="lines[' + lineIndex + '][description]" class="form-control"></td>';
        html += '<td><input type="number" step="0.01" min="0" name="lines[' + lineIndex + '][debit]" class="form-control text-right" value="0" onchange="updateTotals()"></td>';
        html += '<td><input type="number" step="0.01" min="0" name="lines[' + lineIndex + '][credit]" class="form-control text-right" value="0" onchange="updateTotals()"></td>';
        html += '<td><button type="button" class="btn btn-danger btn-sm" onclick="removeLine(this)"><i class="fa fa-times"></i></button></td>';
        html += '</tr>';
        $('#voucher-lines-table tbody').append(html);
        lineIndex++;
        updateTotals();
    }

    function removeLine(btn) {
        if ($('#voucher-lines-table tbody tr').length > 1) {
            $(btn).closest('tr').remove();
            updateTotals();
        }
    }

    function updateTotals() {
        var totalDebit = 0;
        var totalCredit = 0;
        $('#voucher-lines-table tbody tr').each(function() {
            var debit = parseFloat($(this).find('input[name$="[debit]"]').val()) || 0;
            var credit = parseFloat($(this).find('input[name$="[credit]"]').val()) || 0;
            totalDebit += debit;
            totalCredit += credit;
        });
        $('#total-debit').text(totalDebit.toFixed(2));
        $('#total-credit').text(totalCredit.toFixed(2));
    }

    $(document).ready(function() {
        updateTotals();
    });
</script>
@endpush
