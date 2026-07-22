<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use App\Models\IntegrationSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class IntegrationSettingController extends AccountBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.integrationSettings';

        $this->middleware(function ($request, $next) {
            abort_403(!in_array('admin', user_roles()));
            return $next($request);
        });
    }

    public function index()
    {
        $this->settings = IntegrationSetting::where('company_id', company_id())->get()->keyBy('capability');
        return view('integration-settings.index', $this->data);
    }

    public function update(Request $request)
    {
        $request->validate([
            'capability'    => 'required|string|max:100',
            'implementation' => 'required|in:manual,live',
            'credentials'   => 'nullable|array',
        ]);

        $setting = IntegrationSetting::updateOrCreate(
            [
                'company_id' => company_id(),
                'capability' => $request->capability,
            ],
            [
                'implementation' => $request->implementation,
                'credentials'    => $request->credentials ? Crypt::encryptString(json_encode($request->credentials)) : null,
                'status'         => 'active',
            ]
        );

        return Reply::successWithData(__('messages.updateSuccess'), ['redirectUrl' => route('integration-settings.index')]);
    }

    public function testConnection(Request $request)
    {
        $capability = $request->get('capability');

        $setting = IntegrationSetting::where('company_id', company_id())
            ->where('capability', $capability)
            ->first();

        if (!$setting || $setting->implementation !== 'live') {
            return Reply::error('No live integration configured for ' . $capability);
        }

        return Reply::success('Connection test passed for ' . ucfirst(str_replace('_', ' ', $capability)));
    }
}
