<?php

namespace TruckerTracker;

Require_once __DIR__.'/IntegratedTestCase.php';


class MessageTest extends IntegratedTestCase
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
    public function queues_message_and_delete(){

        // Arrange
        $driver = $this->driverset[0];

        // Act
        $this->login();
        $this->byCssSelector('#driver'. $driver['_id'].' button.open-modal-message')->click();
        $this->wait(1000);
        $message_text = 'Hello';
        $this->type($message_text,'#message_text');
        $this->byId('btn-save-message')->click();
        $this->wait(7000);

        // Assert

        $message = $this->getMongoConnection()->collection('messages')->findOne(
            [
                'driver_id' => $driver['_id'],
                'organisation_id' => $this->orgset[0]['_id'],
                'message_text' => $message_text,
                'status' => 'queued'
            ]);

        $this->assertNotNull($message);
        $this->assertThat($this->byId('message'.$message['_id'])->displayed(),$this->isTrue());

        $this
            ->assertThat($this
                ->byCssSelector('#message'.$message['_id'].' .first_name')
                ->text(),$this
                ->equalTo($driver['first_name']));
        $this
            ->assertThat($this
                ->byCssSelector('#message'.$message['_id'].' .last_name')
                ->text(),$this
                ->equalTo($driver['last_name']));
        $this
            ->assertThat($this
                ->byCssSelector('#message'.$message['_id'].' .status')
                ->text(),$this
                ->equalTo('queued'));
        $toDateTime = $message['queued_at']->toDateTime();
        $toDateTime->setTimezone(new \DateTimezone('Australia/Sydney'));
        $dateString = $toDateTime->format($this->orgset[0]['datetime_format']);
        $this
            ->assertThat($this
                ->byCssSelector('#message'.$message['_id'].' .sent_at')
                ->text(),$this
                ->equalTo($dateString));
        $this
            ->assertThat($this
            ->byId('message'.$message['_id'])
            ->attribute('title'), $this
            ->equalTo($message['message_text']));


        // test delete
        $this
            ->byCssSelector('#message'.$message['_id'].' button.delete-message')->click();
        $this->wait(1000);

        $this->notSeeId('message'.$message['_id']);

        $this->notSeeInDatabase('messages',['_id' => $message['_id']]);

    }

}
