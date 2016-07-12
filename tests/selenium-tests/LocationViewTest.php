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
            'locations' => json_decode($this->viewLocationSetJson,true)
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
        $loc = json_decode($this->viewLocationSetJson,true)[0];
        $vehicle = $this->vehicleSet[0];
        $expectedDatetime = (new \DateTime($loc['received_at']))->format($org['datetime_format']);

        // Act
        $this->byCssSelector('#accordion a[href="#locate_vehicles_collapsible"]')->click();
        $this->wait();
        $this->byId('#location'.$loc['_id'])->click();
        $this->wait();
        $this->byCssSelector('button.open-modal-location-view')->click();
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
                ->equalTo($vehicle['registration_number']));

    }
}
