<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/


Route::match(['GET','POST'], '/incoming', function(){

    $twiml = new \Twilio\Twiml\Twiml();
    $twiml->say('Hello - your app just answered the phone. Neat, eh?', array('voice' => 'alice'));
    $response = Response::make($twiml, 200);
    $response->header('Content-Type','text/xml');
    return $response;
});

Route::get('/', function () {
    return view('welcome');
});
