<?php
namespace TruckerTracker;
require_once __DIR__ . '/LocationControllerTestCase.php';

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use TruckerTracker\Twilio\TwilioInterface;

class LocationControllerViewLocationTest extends LocationControllerTestCase
{

    /**
     * @before
     */
    public function setUp()
    {
        parent::setUp();
    }

    /**
     * get location data
     *
     * @test
     */
    public function suppliesSpecificVehicleLocationDataPoint()
    {
        // Arrange
        $org = $this->orgSet[0];
        $user = $this->user();
        $loc = $this->viewLocationSet[0];
        $vehicle = $this->vehicleSet[0];
        $expectedLoc = $loc;
        unset($expectedLoc['organisation_id']);
        unset($expectedLoc['vehicle_id']);
        unset($expectedLoc['sid']);

        $expectedLoc['queued_at'] = (new \DateTime($expectedLoc['queued_at']))->format($org['datetime_format']);
        $expectedLoc['datetime'] = (new \DateTime($expectedLoc['datetime']))->format($org['datetime_format']);
        $expectedLoc['sent_at'] = (new \DateTime($expectedLoc['sent_at']))->format($org['datetime_format']);
        $expectedLoc['delivered_at'] = (new \DateTime($expectedLoc['delivered_at']))->format($org['datetime_format']);
        $expectedLoc['received_at'] = (new \DateTime($expectedLoc['received_at']))->format($org['datetime_format']);

        $vehicle = array_filter($vehicle, function($key) {
            return !in_array($key,['mobile_phone_number','tracker_imei_number','tracker_password','organisation_id']);
        },ARRAY_FILTER_USE_KEY);
        $expectedLoc = array_merge($expectedLoc,['vehicle'=>$vehicle]);

        // Act
        $this->actingAs($user)->json('get','vehicle/location/'. $loc['_id']);

        // Assert
        $this->assertResponseOk();
        
        $this->seeJson($expectedLoc);

    }

 


 
}
