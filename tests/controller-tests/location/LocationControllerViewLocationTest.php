<?php
namespace TruckerTracker;
require_once __DIR__ . '/LocationControllerTestCase.php';

class LocationControllerViewLocationsTest extends LocationControllerTestCase
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
    public function suppliesVehicleLocationDataPoints()
    {
        // Arrange
        $org = $this->orgSet[0];
        $user = $this->user();
        $vehicle = $this->vehicleSet[0];
        $expectedLocSet = [];
        foreach (json_decode($this->viewLocationSetJson,true) as $loc){
            $expectedLoc = $loc;
            unset($expectedLoc['organisation_id']);
            unset($expectedLoc['vehicle_id']);
            unset($expectedLoc['sid']);
            $expectedLoc['vehicle'] = [
                '_id'=>$vehicle['_id'],
                'registration_number'=>$vehicle['registration_number']
            ];
            $expectedLocSet[] = $expectedLoc;
        }


        // Act
        $this->actingAs($user)->json('get','vehicle/locations');

        // Assert
        $this->assertResponseOk();
        
        $this->seeJson($expectedLoc);

    }

    /**
     * get location data
     *
     * @test
     */
    public function suppliesOneVehicleLocationDataPoint()
    {
        // Arrange
        $org = $this->orgSet[0];
        $user = $this->user();
        $vehicle = $this->vehicleSet[0];
        $expectedLoc = json_decode($this->viewLocationSetJson,true)[0];
        unset($expectedLoc['organisation_id']);
        unset($expectedLoc['vehicle_id']);
        unset($expectedLoc['sid']);
        $expectedLoc['queued_at'] = (new \DateTime($expectedLoc['queued_at']))->format('c');
        $expectedLoc['datetime'] = (new \DateTime($expectedLoc['datetime']))->format('c');
        $expectedLoc['sent_at'] = (new \DateTime($expectedLoc['sent_at']))->format('c');
        $expectedLoc['delivered_at'] = (new \DateTime($expectedLoc['delivered_at']))->format('c');
        $expectedLoc['received_at'] = (new \DateTime($expectedLoc['received_at']))->format('c');
        $expectedLoc['vehicle'] = [
            '_id'=>$vehicle['_id'],
            'registration_number'=>$vehicle['registration_number']
        ];

        // Act
        $this->actingAs($user)->json('get','vehicle/location/'.$expectedLoc['_id']);

        // Assert
        $this->assertResponseOk();

        $this->seeJson($expectedLoc);

    }


 
}
