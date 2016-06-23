<?php

namespace TruckerTracker;

require_once __DIR__.'/IntegratedTestCase.php';

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class LocationViewTest extends IntegratedTestCase
{


    protected function getFixture()
    {
        return [
            'users' => $this->fixtureUserset,
            'password_resets' => [],
            'organisations' => $this->orgset,
            'drivers' => $this->driverset,
            'vehicles' => $this->vehicleset,
            'messages' => $this->messageSet,
            'locations' => $this->viewLocationSet
        ];
    }

    /**
     * A basic test example.
     *
     * @return void
     *
     * @test
     */
    public function displaysGoogleMapWithDatestampedLocation()
    {

        // Arrange
        $this->login();
        $loc = $this->viewLocationSet[0];
        $vehicle = $this->vehicleset[0];


        // Act
        $this->findByCssSelector('#location'.$loc['_id'].' button.open-modal-location-view')->click();

        // Assert
        $this->see($vehicle['registration_number']);



    }
}
