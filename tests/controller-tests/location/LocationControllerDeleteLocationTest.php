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
        $user = $this->firstUser();
        $loc = $this->viewLocationSet[0];
        $expectedLoc = $loc;
        unset($expectedLoc['organisation_id']);
        unset($expectedLoc['vehicle_id']);
        $expectedLoc['queued_at'] = (new \DateTime($expectedLoc['queued_at']))->format('H:i:s d/m/y');
        $expectedLoc['datetime'] = (new \DateTime($expectedLoc['datetime']))->format('H:i:s d/m/y');
        $expectedLoc['sent_at'] = (new \DateTime($expectedLoc['sent_at']))->format('H:i:s d/m/y');

        // Act
        $this->actingAs($user)->json('delete','vehicle/location/'. $loc['_id']);

        // Assert
        $this->assertResponseOk();

        $this->seeJson($expectedLoc);

        $this->notSeeInDatabase('locations',['_id'=>$loc['_id']]);

    }


 
}
