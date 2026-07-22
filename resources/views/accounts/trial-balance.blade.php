@extends('layouts.app')

@section('content')
    <div class="content-wrapper">
        <div class="d-flex justify-content-between mb-3">
            <h4>@lang('modules.accounts.trialBalance')</h4>
            <form method="GET" action="{{ route('accounts.trial_balance') }}" class="form-inline">
                <input type="date" name="start_date" class="form-control mr-2" value="{{ request('start_date') }}">
                <input type="date" name="end_date" class="form-control mr-2" value="{{ request('end_date') }}">
                <button type="submit" class="btn btn-primary"><i class="fa fa-filter"></i></button>
            </form>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered table-hover bg-white">
                <thead>
                    <tr>
                        <th>@lang('modules.accounts.accountCode')</th>
                        <th>@lang('modules.accounts.accountName')</th>
                        <th class="text-right">@lang('modules.accounts.debit')</th>
                        <th class="text-right">@lang('modules.accounts.credit')</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($accounts as $account)
                        <tr>
                            <td>{{ $account->code ?? '--' }}</td>
                            <td>{{ $account->name }}</td>
                            <td class="text-right">{{ $account->balance > 0 ? currency_format($account->balance, company()->currency->id) : '--' }}</td>
                            <td class="text-right">{{ $account->balance < 0 ? currency_format(abs($account->balance), company()->currency->id) : '--' }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="bg-light">
                        <th colspan="2">@lang('app.total')</th>
                        <th class="text-right">{{ currency_format($totalDebit, company()->currency->id) }}</th>
                        <th class="text-right">{{ currency_format($totalCredit, company()->currency->id) }}</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
@endsection
