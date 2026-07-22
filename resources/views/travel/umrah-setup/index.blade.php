@extends('layouts.app')

@section('content')
<div class="content-wrapper">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header">
                    <h4>@lang('app.menu.umrahSetup')</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-4">
                            <a href="{{ route('hotels.index') }}" class="btn btn-primary btn-lg btn-block p-4">
                                <i class="fa fa-hotel fa-2x d-block mb-2"></i>@lang('app.menu.hotels')
                            </a>
                        </div>
                        <div class="col-md-4 mb-4">
                            <a href="{{ route('packages.index') }}" class="btn btn-success btn-lg btn-block p-4">
                                <i class="fa fa-box fa-2x d-block mb-2"></i>@lang('app.menu.packages')
                            </a>
                        </div>
                        <div class="col-md-4 mb-4">
                            <a href="{{ route('discounts.index') }}" class="btn btn-info btn-lg btn-block p-4">
                                <i class="fa fa-tag fa-2x d-block mb-2"></i>@lang('app.menu.discounts')
                            </a>
                        </div>
                        <div class="col-md-4 mb-4">
                            <a href="{{ route('airlines.index') }}" class="btn btn-warning btn-lg btn-block p-4">
                                <i class="fa fa-plane fa-2x d-block mb-2"></i>@lang('app.menu.airlines')
                            </a>
                        </div>
                        <div class="col-md-4 mb-4">
                            <a href="{{ route('transporters.index') }}" class="btn btn-danger btn-lg btn-block p-4">
                                <i class="fa fa-truck fa-2x d-block mb-2"></i>@lang('app.menu.transporters')
                            </a>
                        </div>
                        <div class="col-md-4 mb-4">
                            <a href="{{ route('visa-companies.index') }}" class="btn btn-secondary btn-lg btn-block p-4">
                                <i class="fa fa-passport fa-2x d-block mb-2"></i>@lang('app.menu.visaCompanies')
                            </a>
                        </div>
                        <div class="col-md-4 mb-4">
                            <a href="{{ route('service-providers.index') }}" class="btn btn-dark btn-lg btn-block p-4">
                                <i class="fa fa-concierge-bell fa-2x d-block mb-2"></i>@lang('app.menu.serviceProviders')
                            </a>
                        </div>
                        <div class="col-md-4 mb-4">
                            <a href="{{ route('iata.index') }}" class="btn btn-primary btn-lg btn-block p-4">
                                <i class="fa fa-barcode fa-2x d-block mb-2"></i>@lang('app.menu.iata')
                            </a>
                        </div>
                        <div class="col-md-4 mb-4">
                            <a href="{{ route('customer-types.index') }}" class="btn btn-success btn-lg btn-block p-4">
                                <i class="fa fa-users fa-2x d-block mb-2"></i>@lang('app.menu.customerTypes')
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
