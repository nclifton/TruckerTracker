<?php

namespace TruckerTracker\Http\Controllers;

use Auth;
use TruckerTracker\Http\Requests;
use View;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = Auth::user();
        $organisation = $user->organisation;

        $drivers = ($organisation) ? $organisation->drivers : [];
        $vehicles = ($organisation) ? $organisation->vehicles : [];
        $messages = ($organisation) ? $organisation->messages : [];
        $locations = ($organisation) ? $organisation->locations : [];

        $params = [];
        return View::make("home")->with("organisation", $organisation)
            ->with("drivers", $drivers)
            ->with("vehicles", $vehicles)
            ->with('messages', $messages)
            ->with("locations", $locations)
            ->with("params", $params);

    }
}
