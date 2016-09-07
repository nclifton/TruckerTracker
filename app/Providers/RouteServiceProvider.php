<?php

namespace TruckerTracker\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;
use TruckerTracker\Driver;
use TruckerTracker\Location;
use TruckerTracker\Message;
use TruckerTracker\Vehicle;
use TruckerTracker\Organisation;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * This namespace is applied to your controller routes.
     *
     * In addition, it is set as the URL generator's root namespace.
     *
     * @var string
     */
    protected $namespace = 'TruckerTracker\Http\Controllers';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        //

        parent::boot();

        Route::model('driver', Driver::class);
        Route::model('vehicle', Vehicle::class);
        Route::model('organisation', Organisation::class);
        Route::model('location', Location::class);
        Route::model('message', Message::class);

    }

    /**
     * Define the routes for the application.
     *
     * @return void
     */
    public function map()
    {
        $this->mapPublicWebRoutes();

        $this->mapAuthenticatedWebRoutes();

        $this->mapApiRoutes();

        //
    }

    /**
     * Define the "public web" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     *
     * @return void
     */
    protected function mapPublicWebRoutes()
    {
        Route::group([
            'middleware' => ['web'],
            'namespace' => $this->namespace,
        ], function ($router) {
            require base_path('routes/public.php');
        });
    }

    /**
     * Define the "authenticated web" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     *
     * @return void
     */
    protected function mapAuthenticatedWebRoutes()
    {
        Route::group([
            'middleware' => ['web','auth'],
            'namespace' => $this->namespace,
        ], function ($router) {
            require base_path('routes/web.php');
        });
    }

    /**
     * Define the "api" routes for the application.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapApiRoutes()
    {
        Route::group([
            'middleware' => ['api','auth.basic'],
            'namespace' => $this->namespace,
        ], function ($router) {
            require base_path('routes/api.php');
        });
    }
}
