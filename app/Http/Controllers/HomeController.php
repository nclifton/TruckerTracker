<?php

namespace TruckerTracker\Http\Controllers;

use Auth;
use Gate;
use Camroncade\Timezone\Timezone;
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
        $org = $user->organisation;

        if (Gate::denies('view-home', $org)) {
            abort(403,'Permission Denied');
        }

        if($org)
            $org->load('users','drivers','vehicles','messages','locations');

        $params = [];
        $tz = new Timezone();
        return View::make("home")
            ->with("user", $user)
            ->with("org", $org)
            ->with("tzhelper", $tz)
            ->with("params", $params);

    }
}
