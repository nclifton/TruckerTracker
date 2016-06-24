<?php
namespace TruckerTracker;

Require_once __DIR__.'/IntegratedTestCase.php';


class LocationTest extends \TruckerTracker\IntegratedTestCase
{

    protected function getFixture()
    {
         return [
            'users' => $this->fixtureUserset,
            'password_resets' => [],
            'organisations' => $this->orgset,
            'drivers' => [],
            'vehicles' => $this->vehicleset,
            'messages' => [],
            'locations' => []
        ];
    }

    /**
     * @test
     */
    public function queuesLocationRequest_and_deletes(){

        // Arrange
        $org = $this->orgset[0];
        $vehicle = $this->vehicleset[0];
        $_SERVER['SERVER_NAME'] = 'localhost';

        // Act
        $this->login();
        $this->byCssSelector('#vehicle'. $vehicle['_id'].' button.open-modal-location')->click();
        $this->wait(4000);
        $this->byId('btn-save-location')->click();
        $this->wait(10000);

        // Assert

        $doc = $this->getMongoConnection()->collection('locations')->findOne(
            [
                'vehicle_id' => $vehicle['_id'],
                'organisation_id' => $org['_id'],
                'status' => 'queued'
            ]);
        $id = $doc['_id'];

        $this->assertNotNull($id);
        $this->assertThat($this->byId('location'.$id)->displayed(),$this->isTrue());
        $this->assertThat($this->byCssSelector('#location'.$id.' span.registration_number')->text(), $this->equalTo($vehicle['registration_number']));

        $this->byCssSelector('#location'.$id.' button.delete-location')->click();
        $this->wait(4000);
        
        $this->notSeeId('location'.$id);

    }

}
