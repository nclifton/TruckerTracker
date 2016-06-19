<?php
namespace TruckerTracker;

Require_once __DIR__.'/IntegratedTestCase.php';


class SeleniumTestTwilioLocationForm extends \TruckerTracker\IntegratedTestCase
{

    protected function getFixture()
    {
         return [
            'users' => $this->fixtureUserset,
            'password_resets' => [],
            'organisations' => $this->orgset,
            'drivers' => $this->driverset,
            'vehicles' => $this->vehicleset,
            'messages' => $this->messageset,
            'locations' => []
        ];
    }

    /**
     * @test
     */
    public function queuesLocationRequest(){

        // Arrange
        $vehicle = $this->vehicleset[0];

        // Act
        $this->login();
        $this->byCssSelector('#vehicle'. $vehicle['_id'].' button.open-modal-location')->click();
        sleep(1);
        $this->byId('btn-save-location')->click();
        sleep(1);

        // Assert

        $results = $this->getMongoConnection()->collection('locations')->find(
            [
                'vehicle_id' => $vehicle['_id'],
                'organisation_id' => $this->orgset[0]['_id'],
                'status' => 'queued'
            ]);
        $id = null;
        foreach ($results as $doc){
            $id = $doc['_id'];
        }
        $this->assertNotNull($id);
        $this->assertThat($this->byId('location'.$id)->displayed(),$this->isTrue());
        $this->assertThat($this->byCssSelector('#location'.$id.' span.registration_number')->text(), $this->equalTo($vehicle['registration_number']));

    }

}
