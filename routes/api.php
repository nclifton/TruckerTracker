<?php


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


Route::post('/incoming', 'TwilioIncomingController@voice');
Route::get('/incoming', 'TwilioIncomingController@voice');
Route::post('/incoming/message/status', 'TwilioIncomingController@messageStatus');
Route::get('/incoming/message/status', 'TwilioIncomingController@messageStatus');
Route::post('/incoming/location/status', 'TwilioIncomingController@messageStatus');
Route::get('/incoming/location/status', 'TwilioIncomingController@messageStatus');
Route::post('/incoming/message', 'TwilioIncomingController@message');
Route::get('/incoming/message', 'TwilioIncomingController@message');
Route::post('/incoming/location', 'TwilioIncomingController@message');
Route::get('/incoming/location', 'TwilioIncomingController@message');


