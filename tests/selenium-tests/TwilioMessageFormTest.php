<?php

Require_once __DIR__.'/SeleniumTestLoader.php';


class SeleniumTestTwilioMessageForm extends SeleniumTestLoader
{

    protected function getFixture()
    {
        return [
            'users' => $this->fixtureUserset,
            'password_resets' => [],
            'organisations' => $this->orgset,
            'drivers' => $this->driverset,
            'vehicles' => $this->vehicleset,
            'messages' => [],
            'locations' => []
        ];
    }
    
    /**
     * @test
     */
    public function queuesMessage(){

        // Arrange
        $driver = $this->driverset[0];

        // Act
        $this->login();
        $this->byCssSelector('#driver'. $driver['_id'].' button.open-modal-message')->click();
        sleep(1);
        $message_text = 'Hello';
        $this->clearType($message_text,'#message_text');
        $this->byId('btn-save-message')->click();
        sleep(6);

        // Assert

        $results = $this->getMongoConnection()->collection('messages')->find(
            [
                'driver_id' => $driver['_id'],
                'organisation_id' => $this->orgset[0]['_id'],
                'message_text' => $message_text,
                'status' => 'queued'
            ]);
        $id = null;
        foreach ($results as $doc){
            $id = $doc['_id'];
        }
        $this->assertNotNull($id);
        $this->assertThat($this->byId('message'.$id)->displayed(),$this->isTrue());

    }

}
