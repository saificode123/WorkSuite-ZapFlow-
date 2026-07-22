@extends('layouts.app')

@section('content')
<div class="content-wrapper">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header"><h5>@lang('app.menu.integrationSettings')</h5></div>
                <div class="card-body">
                    <p class="text-muted">@lang('modules.integrationSettings.description')</p>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>@lang('app.capability')</th>
                                    <th>@lang('app.currentImplementation')</th>
                                    <th>@lang('app.status')</th>
                                    <th>@lang('app.action')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $capabilities = [
                                        'ticketing' => 'Ticketing (GDS/Sabre)',
                                        'visa_tracking' => 'Visa Tracking',
                                        'iata_lookup' => 'IATA Lookup',
                                        'nusuk_import' => 'NUSUK Import',
                                    ];
                                @endphp
                                @foreach($capabilities as $key => $label)
                                <tr>
                                    <td>{{ $label }}</td>
                                    <td>
                                        <span class="badge badge-{{ ($settings[$key]->implementation ?? 'manual') === 'live' ? 'success' : 'secondary' }}">
                                            {{ ucfirst($settings[$key]->implementation ?? 'manual') }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-{{ ($settings[$key]->status ?? 'inactive') === 'active' ? 'success' : 'warning' }}">
                                            {{ ucfirst($settings[$key]->status ?? 'inactive') }}
                                        </span>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary configure-integration"
                                                data-capability="{{ $key }}"
                                                data-implementation="{{ $settings[$key]->implementation ?? 'manual' }}"
                                                data-credentials="{{ $settings[$key]->has_credentials ?? false }}">
                                            <i class="fa fa-cog"></i> @lang('app.configure')
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="integrationModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">@lang('app.configureIntegration')</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <x-form id="integrationForm" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="capability" id="int-capability">
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <x-forms.select fieldId="implementation" :fieldLabel="__('app.implementationType')" fieldName="implementation">
                                <option value="manual">@lang('app.manual')</option>
                                <option value="live">@lang('app.live')</option>
                            </x-forms.select>
                        </div>
                        <div id="credentials-section" style="display:none;" class="col-md-12">
                            <h6 class="mb-3">@lang('app.apiCredentials')</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <x-forms.text fieldId="api_key" :fieldLabel="__('app.apiKey')" fieldName="credentials[api_key]" />
                                </div>
                                <div class="col-md-6">
                                    <x-forms.text fieldId="api_secret" :fieldLabel="__('app.apiSecret')" fieldName="credentials[api_secret]" />
                                </div>
                                <div class="col-md-12">
                                    <x-forms.text fieldId="api_endpoint" :fieldLabel="__('app.apiEndpoint')" fieldName="credentials[api_endpoint]" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-secondary" id="test-connection-btn">
                            <i class="fa fa-plug"></i> @lang('app.testConnection')
                        </button>
                        <x-forms.button-cancel data-dismiss="modal" class="border-0 mr-3">@lang('app.cancel')</x-forms.button-cancel>
                        <x-forms.button-primary id="save-integration" icon="check">@lang('app.save')</x-forms.button-primary>
                    </div>
                </div>
            </x-form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$('body').on('click', '.configure-integration', function() {
    const capability = $(this).data('capability');
    const implementation = $(this).data('implementation');
    $('#int-capability').val(capability);
    $('#implementation').val(implementation);
    $('#credentials-section').toggle(implementation === 'live');
    $('#integrationModal').modal('show');
});

$('#implementation').change(function() {
    $('#credentials-section').toggle($(this).val() === 'live');
});

$('#save-integration').click(function() {
    $.easyAjax({
        url: "{{ route('integration-settings.update') }}",
        container: '#integrationForm',
        type: 'POST',
        data: $('#integrationForm').serialize(),
        success: function(response) {
            if (response.status === 'success') {
                $('#integrationModal').modal('hide');
                window.location.reload();
            }
        }
    });
});

$('#test-connection-btn').click(function() {
    const capability = $('#int-capability').val();
    $.easyAjax({
        url: "{{ route('integration-settings.test') }}",
        type: 'POST',
        data: { capability, _token: '{{ csrf_token() }}' },
        success: function(response) {
            if (response.status === 'success') {
                window.toastr.success(response.message);
            }
        }
    });
});
</script>
@endpush
