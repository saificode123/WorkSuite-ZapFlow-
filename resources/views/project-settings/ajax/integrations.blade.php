{{-- Create file: resources/views/project-settings/ajax/integrations.blade.php --}}

<div class="col-lg-12 col-md-12 ntfcn-tab-content-left w-100 p-4">
    <x-form id="updateIntegrationSettings" method="POST">

        <!-- JIRA INTEGRATION -->
        <div class="card mb-4">
            <div class="card-header bg-white border-bottom">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0 f-18 text-dark">
                            <i class="fa fa-jira mr-2 text-primary"></i>
                            Jira Integration
                        </h5>
                        <p class="text-muted mb-0 f-12">Connect your Jira workspace to sync tasks</p>
                    </div>
                    <div class="form-group mb-0">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input integration-toggle"
                                   id="jira_status" name="jira_status" value="1"
                                {{ $integrationSettings->jira_status ? 'checked' : '' }}>
                            <label class="custom-control-label f-14" for="jira_status">
                                <span class="status-text">
                                    {{ $integrationSettings->jira_status ? __('app.active') : __('app.inactive') }}
                                </span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-body jira-settings" style="display: {{ $integrationSettings->jira_status ? 'block' : 'none' }}">
                <div class="row">
                    <div class="col-md-6">
                        <x-forms.text
                            :fieldLabel="__('Jira Host')"
                            fieldName="jira_host"
                            fieldId="jira_host"
                            :fieldPlaceholder="__('https://yourcompany.atlassian.net/')"
                            :fieldValue="$integrationSettings->jira_host"
                            fieldRequired="true"
                        />
                    </div>

                    <div class="col-md-6">
                        <x-forms.text
                            :fieldLabel="__('Jira User Email')"
                            fieldName="jira_user"
                            fieldId="jira_user"
                            :fieldPlaceholder="__('your-email@example.com')"
                            :fieldValue="$integrationSettings->jira_user"
                            fieldRequired="true"
                        />
                    </div>

                    <div class="col-md-12">
                        <x-forms.text
                            :fieldLabel="__('Jira API Token')"
                            fieldName="jira_api_key"
                            fieldId="jira_api_key"
                            :fieldPlaceholder="__('Your Jira API token')"
                            :fieldValue="$integrationSettings->jira_api_key"
                            fieldRequired="true"
                        />
                        <small class="text-muted">
                            <i class="fa fa-info-circle"></i>
                            Generate API token from:
                            <a href="https://id.atlassian.com/manage-profile/security/api-tokens" target="_blank">
                                Atlassian Account Settings
                            </a>
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <!-- BUGHERD INTEGRATION -->
        <div class="card mb-4">
            <div class="card-header bg-white border-bottom">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0 f-18 text-dark">
                            <i class="fa fa-bug mr-2 text-danger"></i>
                            BugHerd Integration
                        </h5>
                        <p class="text-muted mb-0 f-12">Connect BugHerd for bug tracking</p>
                    </div>
                    <div class="form-group mb-0">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input integration-toggle"
                                   id="bugherd_status" name="bugherd_status" value="1"
                                {{ $integrationSettings->bugherd_status ? 'checked' : '' }}>
                            <label class="custom-control-label f-14" for="bugherd_status">
                                <span class="status-text">
                                    {{ $integrationSettings->bugherd_status ? __('app.active') : __('app.inactive') }}
                                </span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-body bugherd-settings" style="display: {{ $integrationSettings->bugherd_status ? 'block' : 'none' }}">
                <div class="row">
                    <div class="col-md-6">
                        <x-forms.text
                            :fieldLabel="__('BugHerd Base URL')"
                            fieldName="bugherd_base"
                            fieldId="bugherd_base"
                            :fieldPlaceholder="__('https://www.bugherd.com/api_v2')"
                            :fieldValue="$integrationSettings->bugherd_base"
                            fieldRequired="true"
                        />
                    </div>

                    <div class="col-md-6">
                        <x-forms.text
                            :fieldLabel="__('BugHerd API Key')"
                            fieldName="bugherd_api_key"
                            fieldId="bugherd_api_key"
                            :fieldPlaceholder="__('Your BugHerd API key')"
                            :fieldValue="$integrationSettings->bugherd_api_key"
                            fieldRequired="true"
                        />
                    </div>

                    <div class="col-md-12">
                        <x-forms.text
                            :fieldLabel="__('BugHerd Webhook Token')"
                            fieldName="bugherd_webhook_token"
                            fieldId="bugherd_webhook_token"
                            :fieldPlaceholder="__('Your webhook token')"
                            :fieldValue="$integrationSettings->bugherd_webhook_token"
                        />
                    </div>
                </div>
            </div>
        </div>

        <!-- XERO INTEGRATION -->
        <div class="card mb-4">
            <div class="card-header bg-white border-bottom">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0 f-18 text-dark">
                            <i class="fa fa-file-invoice-dollar mr-2 text-success"></i>
                            Xero Integration
                        </h5>
                        <p class="text-muted mb-0 f-12">Connect Xero for accounting integration</p>
                    </div>
                    <div class="form-group mb-0">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input integration-toggle"
                                   id="xero_status" name="xero_status" value="1"
                                {{ $integrationSettings->xero_status ? 'checked' : '' }}>
                            <label class="custom-control-label f-14" for="xero_status">
                                <span class="status-text">
                                    {{ $integrationSettings->xero_status ? __('app.active') : __('app.inactive') }}
                                </span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-body xero-settings" style="display: {{ $integrationSettings->xero_status ? 'block' : 'none' }}">
                <div class="row">
                    <div class="col-md-6">
                        <x-forms.text
                            :fieldLabel="__('Xero Client ID')"
                            fieldName="xero_client_id"
                            fieldId="xero_client_id"
                            :fieldValue="$integrationSettings->xero_client_id"
                            fieldRequired="true"
                        />
                    </div>

                    <div class="col-md-6">
                        <x-forms.text
                            :fieldLabel="__('Xero Client Secret')"
                            fieldName="xero_client_secret"
                            fieldId="xero_client_secret"
                            :fieldValue="$integrationSettings->xero_client_secret"
                            fieldRequired="true"
                        />
                    </div>

                    <div class="col-md-12">
                        <x-forms.text
                            :fieldLabel="__('Xero Redirect URI')"
                            fieldName="xero_redirect_uri"
                            fieldId="xero_redirect_uri"
                            :fieldPlaceholder="__('https://yoursite.com/xero/callback')"
                            :fieldValue="$integrationSettings->xero_redirect_uri"
                            fieldRequired="true"
                        />
                    </div>

                    <div class="col-md-12">
                        <x-forms.text
                            :fieldLabel="__('Xero Scopes')"
                            fieldName="xero_scopes"
                            fieldId="xero_scopes"
                            :fieldValue="$integrationSettings->xero_scopes ?? 'openid email profile offline_access accounting.transactions'"
                        />
                        <small class="text-muted">
                            <i class="fa fa-info-circle"></i>
                            Space-separated list of Xero OAuth scopes
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <x-forms.button-primary id="save-integration-settings" icon="check">
            @lang('app.save')
        </x-forms.button-primary>

    </x-form>
</div>

<script>
    // Toggle integration settings visibility
    $('.integration-toggle').on('change', function() {
        const integrationName = $(this).attr('id').replace('_status', '');
        const settingsDiv = $('.' + integrationName + '-settings');
        const statusText = $(this).siblings('label').find('.status-text');

        if ($(this).is(':checked')) {
            settingsDiv.slideDown();
            statusText.text('{{ __("app.active") }}');
        } else {
            settingsDiv.slideUp();
            statusText.text('{{ __("app.inactive") }}');
        }
    });

    // Save integration settings
    $('#save-integration-settings').click(function(e) {
        e.preventDefault();

        var formData = $('#updateIntegrationSettings').serialize();

        $.easyAjax({
            url: "{{ route('project-settings.update-integrations') }}",
            container: '#updateIntegrationSettings',
            type: "POST",  // Keep this as POST
            disableButton: true,
            blockUI: true,
            buttonSelector: "#save-integration-settings",
            data: formData,
            success: function(response) {
                if (response.status == 'success') {
                    // Optionally reload the page or show success message
                    window.location.reload();
                }
            }
        });
    });
</script>
