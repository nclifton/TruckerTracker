<?php
namespace TruckerTracker;
require_once __DIR__ . '/LocationControllerTestCase.php';

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use TruckerTracker\Twilio\TwilioInterface;

class LocationControllerDeleteLocationTest extends LocationControllerTestCase
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
    public function deleteVehicleLocationDataPoint()
    {
        // Arrange
        $org = $this->orgSet[0];
        $user = $this->firstUser();

        $expectedLoc = json_decode($this->viewLocationSetJson,true)[0];
        unset($expectedLoc['organisation_id']);
        unset($expectedLoc['vehicle_id']);
        unset($expectedLoc['sid']);

        // Act
        $this->actingAs($user)->json('delete','vehicle/location/'. $expectedLoc['_id']);

        // Assert
        $this->assertResponseOk();

        $this->seeJson($expectedLoc);

        $this->notSeeInDatabase('locations',['_id'=>$expectedLoc['_id']]);

    }


 
}
