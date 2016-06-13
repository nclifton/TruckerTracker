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

Route::get('/incoming', 'TwilioIncomingController@voice');
Route::post('/incoming', 'TwilioIncomingController@voice');
Route::post('/incoming/message/status', 'TwilioIncomingController@messageStatus');
Route::get('/incoming/message/status', 'TwilioIncomingController@messageStatus');
Route::post('/incoming/message', 'TwilioIncomingController@message');
Route::get('/incoming/message', 'TwilioIncomingController@message');

Route::get('/', function () {
    return view('welcome');
});

Route::auth();

Route::get('/home', 'HomeController@index');

Route::get('/organisation/{organisation}', 'ConfigController@getOrganisation');
Route::post('/organisation', 'ConfigController@addOrganisation');
Route::put('/organisation/{organisation}', 'ConfigController@updateOrganisation');
Route::put('/organisation/adduser/{organisation}', 'ConfigController@addOrganisationUser');

Route::get('/drivers/{driver}', 'ConfigController@getDriver');
Route::post('/drivers', 'ConfigController@addDriver');
Route::put('/drivers/{driver}', 'ConfigController@updateDriver');
Route::delete('/drivers/{driver}','ConfigController@deleteDriver');

Route::get('/vehicles/{vehicle}', 'ConfigController@getVehicle');
Route::post('/vehicles', 'ConfigController@addVehicle');
Route::put('/vehicles/{vehicle}', 'ConfigController@updateVehicle');
Route::delete('/vehicles/{vehicle}', 'ConfigController@deleteVehicle');

Route::post('/text/driver/{driver}', 'TwilioController@messageDriver');
Route::post('/text/vehicle/{vehicle}', 'TwilioController@locateVehicle');



