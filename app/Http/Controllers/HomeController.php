<?php

namespace TruckerTracker\Http\Controllers;

use Auth;
use Config;
use Gate;
use Camroncade\Timezone\Timezone;
use Log;
use TruckerTracker\Http\Requests;
use TruckerTracker\Twilio\TwilioHelper;
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
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = Auth::user();
        $org = $user->organisation()->first();

        if (Gate::denies('view-home', $org)) {
            abort(403,'Permission Denied');
        }

        if($org) {
            $org->load('users', 'drivers', 'vehicles', 'messages.driver', 'locations.vehicle');
            $twilio_username = $org->twilioUser->username;
            $twilio_user_password = $org->twilio_user_password;
        } else {
            $twilio_username = bin2hex(random_bytes(16));
            $twilio_user_password = bin2hex(random_bytes(16));
        }

        $twilio_inbound_message_request_url = TwilioHelper::MessageRequestUrl($twilio_username,$twilio_user_password);
        $twilio_outbound_message_status_callback_url = TwilioHelper::MessageStatusCallbackUrl($twilio_username,$twilio_user_password);
        
        $tz = new Timezone();

        Log::debug($org?$org->toJson():'new user');

        return View::make('home')
            ->with('user', $user)
            ->with('org', $org)
            ->with('tzhelper', $tz)
            ->with('twilio_inbound_message_request_url',$twilio_inbound_message_request_url)
            ->with('twilio_outbound_message_status_callback_url',$twilio_outbound_message_status_callback_url)
            ->with('twilio_username',$twilio_username)
            ->with('twilio_user_password',$twilio_user_password);

    }
}
