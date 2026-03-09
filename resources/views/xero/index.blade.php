@extends('layouts.app')

@section('content')
    <div class="content-wrapper">
        <div class="d-flex">
            <div id="table-actions" class="flex-grow-1 align-items-center">
                <h4 class="f-21 font-weight-normal text-capitalize mb-0 w-100 mb-3">
                    <i class="fa fa-link"></i> Xero Integration
                </h4>
            </div>
        </div>

        <div class="d-flex flex-column w-tables rounded mt-3 bg-white">
            <div class="row p-4">
                <div class="col-md-12">
                    @if(session('success'))
                        <x-alert type="success">{{ session('success') }}</x-alert>
                    @endif

                    @if(session('error'))
                        <x-alert type="danger">{{ session('error') }}</x-alert>
                    @endif

                    @if($xeroConnected)
                        <div class="card border-success">
                            <div class="card-header bg-success text-white">
                                <h5 class="mb-0"><i class="fa fa-check-circle"></i> Connected to Xero</h5>
                            </div>
                            <div class="card-body">
                                @if($xeroToken)
                                    <p><strong>Tenant:</strong> {{ $xeroToken->tenant_name ?? 'N/A' }}</p>
                                    <p><strong>Connected:</strong> {{ $xeroToken->created_at->format('d M Y, h:i A') }}</p>
                                    <p><strong>Token Expires:</strong> {{ $xeroToken->token_expires_at->format('d M Y, h:i A') }}</p>
                                @endif

                                <div class="mt-3">
                                    <button type="button" class="btn btn-primary" id="test-connection">
                                        <i class="fa fa-plug"></i> Test Connection
                                    </button>
                                    <button type="button" class="btn btn-danger" id="disconnect-xero">
                                        <i class="fa fa-unlink"></i> Disconnect
                                    </button>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="card border-warning">
                            <div class="card-header bg-warning">
                                <h5 class="mb-0"><i class="fa fa-exclamation-triangle"></i> Not Connected</h5>
                            </div>
                            <div class="card-body">
                                <p>Connect your Xero account to automatically sync invoices.</p>
                                <a href="{{ route('xero.connect') }}" class="btn btn-success">
                                    <i class="fa fa-link"></i> Connect to Xero
                                </a>
                            </div>
                        </div>
                    @endif

                    <div class="card mt-4">
                        <div class="card-header">
                            <h5 class="mb-0">How it works</h5>
                        </div>
                        <div class="card-body">
                            <ul>
                                <li>When you create an invoice, it will automatically be created in Xero</li>
                                <li>Contact information will be synced to Xero</li>
                                <li>Invoice items, taxes, and amounts will be synced</li>
                                <li>You can manually sync existing invoices from the invoice details page</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            $('#test-connection').click(function() {
                $.easyAjax({
                    url: "{{ route('xero.test') }}",
                    type: "POST",
                    data: {_token: '{{ csrf_token() }}'},
                    success: function(response) {
                        if(response.status == 'success') {
                            Swal.fire('Success!', response.message, 'success');
                        }
                    }
                });
            });

            $('#disconnect-xero').click(function() {
                Swal.fire({
                    title: 'Are you sure?',
                    text: "This will disconnect your Xero integration",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, disconnect',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.easyAjax({
                            url: "{{ route('xero.disconnect') }}",
                            type: "POST",
                            data: {_token: '{{ csrf_token() }}'},
                            success: function(response) {
                                window.location.reload();
                            }
                        });
                    }
                });
            });
        });
    </script>
@endsection
