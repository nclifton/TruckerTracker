<?php

namespace TruckerTracker\Providers;

use Illuminate\Support\ServiceProvider;
use TruckerTracker\Twilio\Twilio;
use TruckerTracker\Twilio\TwilioInterface;

class TruckerTrackerServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('twilio', function () {
            return $this->app->make(Twilio::class);
        });

    }
}
