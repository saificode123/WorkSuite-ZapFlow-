@extends('layouts.app')

@section('content')
    <div class="content-wrapper">
        <div class="d-flex justify-content-between mb-3">
            <h4>@lang('modules.accounts.ledger')</h4>
            <form method="GET" action="{{ route('accounts.ledger') }}" class="form-inline">
                <select name="account_id" class="form-control mr-2 select-picker" data-live-search="true">
                    <option value="">@lang('app.select') @lang('modules.accounts.account')</option>
                    @foreach($accounts as $account)
                        <option value="{{ $account->id }}" @if(request('account_id') == $account->id) selected @endif>{{ $account->name }} ({{ $account->code }})</option>
                    @endforeach
                </select>
                <input type="date" name="start_date" class="form-control mr-2" value="{{ request('start_date') }}">
                <input type="date" name="end_date" class="form-control mr-2" value="{{ request('end_date') }}">
                <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i></button>
            </form>
        </div>

        @isset($selectedAccount)
            <div class="card bg-white b-shadow-4">
                <div class="card-header">
                    <h5>{{ $selectedAccount->name }}</h5>
                    <small>@lang('modules.accounts.openingBalance'): {{ currency_format($openingBalance, company()->currency->id) }}</small>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>@lang('app.date')</th>
                                <th>@lang('modules.accounts.voucherNumber')</th>
                                <th>@lang('modules.accounts.description')</th>
                                <th class="text-right">@lang('modules.accounts.debit')</th>
                                <th class="text-right">@lang('modules.accounts.credit')</th>
                                <th class="text-right">@lang('modules.accounts.balance')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $runningBalance = $openingBalance; @endphp
                            <tr class="bg-light">
                                <td colspan="5"><strong>@lang('modules.accounts.openingBalance')</strong></td>
                                <td class="text-right"><strong>{{ currency_format($runningBalance, company()->currency->id) }}</strong></td>
                            </tr>
                            @forelse($ledgerEntries ?? [] as $entry)
                                @php $runningBalance += ($entry->debit - $entry->credit); @endphp
                                <tr>
                                    <td>{{ $entry->journalVoucher->date->format(company()->date_format) }}</td>
                                    <td>{{ $entry->journalVoucher->voucher_number }}</td>
                                    <td>{{ $entry->description ?? '--' }}</td>
                                    <td class="text-right">{{ $entry->debit > 0 ? currency_format($entry->debit, company()->currency->id) : '--' }}</td>
                                    <td class="text-right">{{ $entry->credit > 0 ? currency_format($entry->credit, company()->currency->id) : '--' }}</td>
                                    <td class="text-right">{{ currency_format($runningBalance, company()->currency->id) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center">@lang('messages.noRecordFound')</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            <div class="alert alert-info">@lang('messages.selectAccountToViewLedger')</div>
        @endisset
    </div>
@endsection
