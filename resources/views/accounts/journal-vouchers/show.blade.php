@extends('layouts.app')

@section('content')
    <div class="content-wrapper">
        <div class="row">
            <div class="col-sm-12">
                <div class="card bg-white border-0 b-shadow-4">
                    <div class="card-header bg-white border-bottom-grey p-3">
                        <h4 class="mb-0 f-18">@lang('modules.accounts.journalVoucher') #{{ $voucher->voucher_number }}</h4>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <strong>@lang('app.date'):</strong> {{ $voucher->date->format(company()->date_format) }}
                            </div>
                            <div class="col-md-3">
                                <strong>@lang('modules.accounts.financialYear'):</strong> {{ $voucher->financialYear->name ?? '--' }}
                            </div>
                            <div class="col-md-3">
                                <strong>@lang('modules.accounts.isBalanced'):</strong>
                                {!! $voucher->is_balanced ? '<span class="badge badge-success">' . __('app.yes') . '</span>' : '<span class="badge badge-danger">' . __('app.no') . '</span>' !!}
                            </div>
                            <div class="col-md-3">
                                <strong>@lang('app.createdBy'):</strong> {{ $voucher->createdBy->name ?? '--' }}
                            </div>
                        </div>
                        @if($voucher->narration)
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <strong>@lang('modules.accounts.narration'):</strong> {{ $voucher->narration }}
                                </div>
                            </div>
                        @endif
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>@lang('modules.accounts.account')</th>
                                        <th>@lang('modules.accounts.description')</th>
                                        <th class="text-right">@lang('modules.accounts.debit')</th>
                                        <th class="text-right">@lang('modules.accounts.credit')</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($voucher->lines as $line)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $line->account->name ?? '--' }}</td>
                                            <td>{{ $line->description ?? '--' }}</td>
                                            <td class="text-right">{{ $line->debit > 0 ? currency_format($line->debit, company()->currency->id) : '--' }}</td>
                                            <td class="text-right">{{ $line->credit > 0 ? currency_format($line->credit, company()->currency->id) : '--' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="3" class="text-right">@lang('app.total')</th>
                                        <th class="text-right">{{ currency_format($voucher->lines->sum('debit'), company()->currency->id) }}</th>
                                        <th class="text-right">{{ currency_format($voucher->lines->sum('credit'), company()->currency->id) }}</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer bg-white border-top-grey p-3">
                        <a href="{{ route('journal-vouchers.print', $voucher->id) }}" class="btn btn-primary" target="_blank">
                            <i class="fa fa-print"></i> @lang('app.print')
                        </a>
                        <a href="{{ route('journal-vouchers.index') }}" class="btn btn-secondary">@lang('app.back')</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
