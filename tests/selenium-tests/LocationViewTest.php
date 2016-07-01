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
            'organisations' => $this->orgSet,
            'drivers' => $this->driverSet,
            'vehicles' => $this->vehicleSet,
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
        $org = $this->orgSet[0];
        $loc = $this->viewLocationSet[0];
        $vehicle = $this->vehicleSet[0];
        $expectedDatetime = (new \DateTime($loc['datetime']))->format($org['datetime_format']);

        // Act
        $this->findByCssSelector('#location'.$loc['_id'].' button.open-modal-location-view')->click();
        $this->wait(8000);

        // Assert
        $this
            ->assertThat($this
                ->byId('view_location_vehicle_registration_number')
                ->text(),$this
                ->equalTo($vehicle['registration_number']));
        $this
            ->assertThat($this
                ->byId('view_location_datetime')
                ->text(),$this->equalTo($expectedDatetime));

        $this
            ->assertThat($this
                ->byCssSelector('.gmnoprint[title^="'.$vehicle['registration_number'].'"]')
                ->attribute('title'), $this
                ->equalTo($vehicle['registration_number'].' 0° at 0.5204 km/h'));

    }
}
