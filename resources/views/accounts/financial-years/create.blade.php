@extends('layouts.app')

@section('content')
    <div class="content-wrapper">
        <div class="row">
            <div class="col-sm-12">
                <x-form method="POST" action="{{ route('financial-years.store') }}">
                    <div class="row">
                        <div class="col-md-4">
                            <x-forms.text fieldId="name" :fieldLabel="__('app.name')" fieldName="name" :fieldRequired="true" />
                        </div>
                        <div class="col-md-4">
                            <x-forms.datepicker fieldId="start_date" :fieldLabel="__('modules.accounts.startDate')" fieldName="start_date" :fieldRequired="true" />
                        </div>
                        <div class="col-md-4">
                            <x-forms.datepicker fieldId="end_date" :fieldLabel="__('modules.accounts.endDate')" fieldName="end_date" :fieldRequired="true" />
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
