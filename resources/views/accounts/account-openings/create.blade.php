@extends('layouts.app')

@section('content')
    <div class="content-wrapper">
        <div class="row">
            <div class="col-sm-12">
                <x-form method="POST" action="{{ route('account-openings.store') }}">
                    <div class="row">
                        <div class="col-md-4">
                            <x-forms.select fieldId="account_id" :fieldLabel="__('modules.accounts.account')" fieldName="account_id" :fieldRequired="true">
                                <option value="">@lang('app.select')</option>
                                @foreach($accounts as $account)
                                    <option value="{{ $account->id }}">{{ $account->name }} ({{ $account->code }})</option>
                                @endforeach
                            </x-forms.select>
                        </div>
                        <div class="col-md-4">
                            <x-forms.select fieldId="financial_year_id" :fieldLabel="__('modules.accounts.financialYear')" fieldName="financial_year_id" :fieldRequired="true">
                                <option value="">@lang('app.select')</option>
                                @foreach($financialYears as $fy)
                                    <option value="{{ $fy->id }}">{{ $fy->name }}</option>
                                @endforeach
                            </x-forms.select>
                        </div>
                        <div class="col-md-4">
                            <x-forms.number fieldId="opening_balance" :fieldLabel="__('modules.accounts.openingBalance')" fieldName="opening_balance" :fieldRequired="true" />
                        </div>
                    </div>
                    <div class="w-100 border-top-grey mt-3 pt-3">
                        <x-forms.button-primary icon="check">@lang('app.save')</x-forms.button-primary>
                    </div>
                </x-form>
            </div>
        </div>
    </div>
@endsection
