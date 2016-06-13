<?php

Require_once __DIR__.'/SeleniumTestLoader.php';


class SeleniumTestTwilioLocateForm extends SeleniumTestLoader
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
            'locations' => $this->locationset
        ];
    }

    /**
     * @test
     */
    public function homePage(){

        // Arrange


        // Act
        $this->login();


        // Assert

        $results = $this->getMongoConnection()->collection('locations')->find(
            [
                'organisation_id' => $this->orgset[0]['_id'],
                'status' => 'sent'
            ]);
        $id = null;
        foreach ($results as $doc){
            $id = $doc['_id'];
        }
        $this->assertNotNull($id);
        $this->assertThat($this->byId('location'.$id)->displayed(),$this->isTrue());

    }

}
