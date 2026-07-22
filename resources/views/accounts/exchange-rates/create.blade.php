@extends('layouts.app')

@section('content')
    <div class="content-wrapper">
        <div class="row">
            <div class="col-sm-12">
                <x-form method="POST" action="{{ route('exchange-rates.store') }}">
                    <div class="row">
                        <div class="col-md-4">
                            <x-forms.text fieldId="currency_code" :fieldLabel="__('modules.accounts.currencyCode')" fieldName="currency_code" :fieldRequired="true" />
                        </div>
                        <div class="col-md-4">
                            <x-forms.number fieldId="rate_to_base" :fieldLabel="__('modules.accounts.rateToBase')" fieldName="rate_to_base" :fieldRequired="true" />
                        </div>
                        <div class="col-md-4">
                            <x-forms.datepicker fieldId="effective_date" :fieldLabel="__('modules.accounts.effectiveDate')" fieldName="effective_date" :fieldValue="now()->format(company()->date_format)" :fieldRequired="true" />
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
