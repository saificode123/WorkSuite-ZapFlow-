@extends('layouts.app')

@push('css')
<style>
.setup-tile {
    display: flex;
    align-items: center;
    gap: 14px;
    background: #fff;
    border: 1px solid #eef0f6;
    border-radius: 12px;
    padding: 18px 20px;
    text-decoration: none;
    color: #2b2f42;
    height: 100%;
    transition: transform .15s ease, box-shadow .15s ease, border-color .15s ease;
}
.setup-tile:hover, .setup-tile:focus {
    transform: translateY(-3px);
    box-shadow: 0 8px 24px rgba(0,0,0,.08);
    text-decoration: none;
    color: #2b2f42;
}
.setup-tile-icon {
    width: 48px; height: 48px;
    border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 20px;
    flex-shrink: 0;
}
.setup-tile-label { font-weight: 600; font-size: 14.5px; flex-grow: 1; }
.setup-tile-arrow { color: #c7cbe0; font-size: 12px; transition: transform .15s ease, color .15s ease; }
.setup-tile:hover .setup-tile-arrow { transform: translateX(3px); color: #8a90b3; }

.tile-primary   .setup-tile-icon { background: rgba(13,110,253,.12); color: #0d6efd; }
.tile-success   .setup-tile-icon { background: rgba(25,135,84,.12);  color: #198754; }
.tile-info      .setup-tile-icon { background: rgba(13,202,240,.16); color: #025058; } /* #025058: 5.08:1 contrast vs #34d2f2 (16% alpha on white) — WCAG AA ✓ */
.tile-warning   .setup-tile-icon { background: rgba(255,193,7,.18);  color: #b98900; }
.tile-danger    .setup-tile-icon { background: rgba(220,53,69,.12);  color: #dc3545; }
.tile-secondary .setup-tile-icon { background: rgba(108,117,125,.14); color: #6c757d; }
.tile-dark      .setup-tile-icon { background: rgba(33,37,41,.1);    color: #212529; }
</style>
@endpush

@section('content')
<div class="content-wrapper">
    <div class="row">
        <div class="col-sm-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
                    <h4 class="mb-1"><i class="fa fa-cogs mr-2 text-primary"></i>@lang('app.menu.umrahSetup')</h4>
                    <p class="text-muted f-13 mb-0">Manage the building blocks used across your Umrah packages.</p>
                </div>
                <div class="card-body px-4 pb-4">
                    <div class="row">
                        <div class="col-md-4 col-sm-6 mb-3">
                            <a href="{{ route('hotels.index') }}" class="setup-tile tile-primary">
                                <div class="setup-tile-icon"><i class="fa fa-hotel"></i></div>
                                <div class="setup-tile-label">@lang('app.menu.hotels')</div>
                                <i class="fa fa-chevron-right setup-tile-arrow"></i>
                            </a>
                        </div>
                        <div class="col-md-4 col-sm-6 mb-3">
                            <a href="{{ route('packages.index') }}" class="setup-tile tile-success">
                                <div class="setup-tile-icon"><i class="fa fa-box"></i></div>
                                <div class="setup-tile-label">@lang('app.menu.packages')</div>
                                <i class="fa fa-chevron-right setup-tile-arrow"></i>
                            </a>
                        </div>
                        <div class="col-md-4 col-sm-6 mb-3">
                            <a href="{{ route('discounts.index') }}" class="setup-tile tile-info">
                                <div class="setup-tile-icon"><i class="fa fa-tag"></i></div>
                                <div class="setup-tile-label">@lang('app.menu.discounts')</div>
                                <i class="fa fa-chevron-right setup-tile-arrow"></i>
                            </a>
                        </div>
                        <div class="col-md-4 col-sm-6 mb-3">
                            <a href="{{ route('airlines.index') }}" class="setup-tile tile-warning">
                                <div class="setup-tile-icon"><i class="fa fa-plane"></i></div>
                                <div class="setup-tile-label">@lang('app.menu.airlines')</div>
                                <i class="fa fa-chevron-right setup-tile-arrow"></i>
                            </a>
                        </div>
                        <div class="col-md-4 col-sm-6 mb-3">
                            <a href="{{ route('transporters.index') }}" class="setup-tile tile-danger">
                                <div class="setup-tile-icon"><i class="fa fa-truck"></i></div>
                                <div class="setup-tile-label">@lang('app.menu.transporters')</div>
                                <i class="fa fa-chevron-right setup-tile-arrow"></i>
                            </a>
                        </div>
                        <div class="col-md-4 col-sm-6 mb-3">
                            <a href="{{ route('visa-companies.index') }}" class="setup-tile tile-secondary">
                                <div class="setup-tile-icon"><i class="fa fa-passport"></i></div>
                                <div class="setup-tile-label">@lang('app.menu.visaCompanies')</div>
                                <i class="fa fa-chevron-right setup-tile-arrow"></i>
                            </a>
                        </div>
                        <div class="col-md-4 col-sm-6 mb-3">
                            <a href="{{ route('service-providers.index') }}" class="setup-tile tile-dark">
                                <div class="setup-tile-icon"><i class="fa fa-concierge-bell"></i></div>
                                <div class="setup-tile-label">@lang('app.menu.serviceProviders')</div>
                                <i class="fa fa-chevron-right setup-tile-arrow"></i>
                            </a>
                        </div>
                        <div class="col-md-4 col-sm-6 mb-3">
                            <a href="{{ route('iata.index') }}" class="setup-tile tile-primary">
                                <div class="setup-tile-icon"><i class="fa fa-barcode"></i></div>
                                <div class="setup-tile-label">@lang('app.menu.iata')</div>
                                <i class="fa fa-chevron-right setup-tile-arrow"></i>
                            </a>
                        </div>
                        <div class="col-md-4 col-sm-6 mb-3">
                            <a href="{{ route('customer-types.index') }}" class="setup-tile tile-success">
                                <div class="setup-tile-icon"><i class="fa fa-users"></i></div>
                                <div class="setup-tile-label">@lang('app.menu.customerTypes')</div>
                                <i class="fa fa-chevron-right setup-tile-arrow"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection