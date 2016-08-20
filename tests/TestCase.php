<?php

namespace TruckerTracker;

use Artisan;
use Illuminate\Contracts\Console\Kernel;

abstract class TestCase extends \Illuminate\Foundation\Testing\TestCase
{

    use TestTrait;

    protected function refreshApplication()
    {
        putenv('APP_ENV=testing');

        if (env('APP_ENV') != 'testing'){
            throw new \Exception("environment not set to testing");
        }

        $this->app = $this->createApplication();
    }

    /**
     * The base URL to use while testing the application.
     *
     * @var string
     */
    protected $baseUrl = 'http://local.truckertracker.services';

    public function setUp()
    {
        parent::setUp();
        Artisan::call('migrate');
        $this->artisanSeedDb();
    }


    /**
     * Creates the application.
     *
     * @return \Illuminate\Foundation\Application
     */
    public function createApplication()
    {
        $app = require __DIR__.'/../bootstrap/app.php';
        $app->make(Kernel::class)->bootstrap();
        return $app;
    }


    public function tearDown()
    {
        Artisan::call('migrate:rollback');
    }


}
