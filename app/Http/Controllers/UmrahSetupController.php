<?php

namespace App\Http\Controllers;

class UmrahSetupController extends AccountBaseController
{

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.umrahSetup';

        $this->middleware(function ($request, $next) {
            abort_403(!in_array('umrah_setup', $this->user->modules));
            return $next($request);
        });
    }

    public function index()
    {
        return view('travel.umrah-setup.index', $this->data);
    }

}
