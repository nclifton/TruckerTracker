<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of the routes that are handled
| by your application. Just tell Laravel the URIs it should respond
| to using a Closure or controller method. Build something great!
|
*/



Route::get('/dash',                             'HomeController@index');

Route::get('/organisation/{organisation}',      'ConfigController@getOrganisation');
Route::post('/organisation',                    'ConfigController@addOrganisation');
Route::put('/organisation/{organisation}',      'ConfigController@updateOrganisation');

Route::post('/user',                            'ConfigController@addUser');
Route::put('/user/{user}',                      'ConfigController@updateUser');
Route::delete('/user/{user}',                   'ConfigController@deleteUser');
Route::get('/user/{user}',                      'ConfigController@getUser');

Route::get('/drivers/{driver}',                 'ConfigController@getDriver');
Route::post('/drivers',                         'ConfigController@addDriver');
Route::put('/drivers/{driver}',                 'ConfigController@updateDriver');
Route::delete('/drivers/{driver}',              'ConfigController@deleteDriver');

Route::get('/vehicles/{vehicle}',               'ConfigController@getVehicle');
Route::post('/vehicles',                        'ConfigController@addVehicle');
Route::put('/vehicles/{vehicle}',               'ConfigController@updateVehicle');
Route::delete('/vehicles/{vehicle}',            'ConfigController@deleteVehicle');

Route::post('/driver/{driver}/message',         'TwilioController@messageDriver');
Route::post('/vehicle/{vehicle}/location',      'TwilioController@locateVehicle');

Route::get('/vehicle/locations',                'LocationController@getLocations');
Route::get('/vehicle/location/{location}',      'LocationController@getLocation');
Route::delete('/vehicle/location/{location}',   'LocationController@deleteLocation');

Route::get('/driver/message/{message}',         'MessageController@getMessage');
Route::delete('/driver/message/{message}',      'MessageController@deleteMessage');

Route::post('/conversation',                    'MessageController@getConversation');
