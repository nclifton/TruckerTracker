<?php

namespace TruckerTracker\Providers;

use TruckerTracker\Twilio\Twilio;
use TruckerTracker\Twilio\TwilioInterface;
use Validator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {

        // validator for Australian Drivers Licences
        Validator::extend('drvlic', function($attribute,$value,$parameters,$validator){
            return preg_match('/^[A-Z0-9]{4,9}$/',$value)   // length and alphanumeric
            && preg_match('/^..[0-9]{2}/',$value)           // 3rd+4th are numeric
            && preg_match('/(.*[0-9]){4}/',$value)          // at least 4 numeric
            && !preg_match('/(.*[A-Z]){3}/',$value);        // no more than 2 alpha


        });
        // validator for Australian Vehicle Registration plates
        Validator::extend('rego', function($attribute,$value,$parameters,$validator){
            return preg_match('/^(?=.*\d)(?=.*[A-Z])[A-Z\d]{6,7}$/',$value);
        });

        // validator for IMEI numbers
        Validator::extend('imei', function($attribute,$value,$parameters,$validator){
            return preg_match('/^\d{15,16}$/',$value)
                || preg_match('/^((\d{6,6}\/\d{2,2})|(\d{2,2}\/\d{6,6}))(\/\d{6,6}\/\d{1,2})$/',$value)
                || preg_match('/^((\d{6,6}-\d{2,2})|(\d{2,2}-\d{6,6}))(-\d{6,6}-\d{1,2})$/',$value);
        });

        // validator for australian phone numbers
        Validator::extend('ausphone', function($attribute,$value,$parameters,$validator){
            return preg_match('/^(\+?61[\d]{9})|(0[\d]{9})$/',$value);
        });

    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
