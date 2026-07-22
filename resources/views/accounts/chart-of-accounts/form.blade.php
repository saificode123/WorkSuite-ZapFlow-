@php
    $edit = isset($account);
@endphp

<x-form method="{{ $edit ? 'PUT' : 'POST' }}" action="{{ $edit ? route('chart-of-accounts.update', $account->id) : route('chart-of-accounts.store') }}">
    <div class="row">
        <div class="col-md-4">
            <x-forms.text fieldId="name" :fieldLabel="__('app.name')" fieldName="name" :fieldValue="$edit ? $account->name : ''" :fieldRequired="true" />
        </div>
        <div class="col-md-4">
            <x-forms.text fieldId="code" :fieldLabel="__('app.code')" fieldName="code" :fieldValue="$edit ? $account->code : ''" />
        </div>
        <div class="col-md-4">
            <x-forms.select fieldId="type" :fieldLabel="__('modules.accounts.type')" fieldName="type" :fieldRequired="true">
                @foreach ($types as $type)
                    <option value="{{ $type }}" @if($edit && $account->type == $type) selected @endif>{{ ucfirst($type) }}</option>
                @endforeach
            </x-forms.select>
        </div>
        <div class="col-md-4">
            <x-forms.select fieldId="parent_id" :fieldLabel="__('modules.accounts.parentAccount')" fieldName="parent_id">
                <option value="">@lang('app.none')</option>
                @foreach ($parents as $type => $accounts)
                    <optgroup label="{{ ucfirst($type) }}">
                        @foreach ($accounts as $parent)
                            <option value="{{ $parent->id }}" @if($edit && $account->parent_id == $parent->id) selected @endif>{{ $parent->name }}</option>
                        @endforeach
                    </optgroup>
                @endforeach
            </x-forms.select>
        </div>
        <div class="col-md-4">
            <x-forms.checkbox fieldId="is_bank_account" :fieldLabel="__('modules.accounts.isBankAccount')" fieldName="is_bank_account"
                :checked="$edit ? $account->is_bank_account : false" />
        </div>
    </div>

    <div class="w-100 border-top-grey mt-3 pt-3">
        <x-forms.button-primary icon="check">@lang('app.save')</x-forms.button-primary>
    </div>
</x-form>
