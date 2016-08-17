<?php

namespace TruckerTracker;

Require_once __DIR__ . '/IntegratedTestCase.php';


class ConversationTest extends IntegratedTestCase
{

    protected function getFixture()
    {

        return [
            'users' =>              $this->fixtureUserSet,
            'password_resets' =>    [],
            'organisations' =>      $this->orgSet,
            'drivers' =>            $this->driverSet,
            'vehicles' =>           [],
            'messages' =>           $this->conversationSet(),
            'locations' =>          []
        ];
    }

    /**
     * @test
     */
    public function displays_conversation()
    {

        // Arrange
        $driver1 = $this->driverSet[0];
        $driver2 = $this->driverSet[1];

        $org = $this->orgSet[0];

        $dbOrg = $this->connection->collection('organisations')->findOne(['_id' => $org['_id']]);
        $twilioUser = $this->connection->collection('users')->findOne(['_id' => $dbOrg['twilio_user_id']]);
        $message_text = 'Hello';
        $reply_message_text = 'Ahoy hoy!';

        // Act
        $this->login();
        $this->byCssSelector('a[href="#message_drivers_collapsible"]')->click();
        $this->wait();
        $this->byId('driver' . $driver1['_id'])->click();
        $this->byId('driver' . $driver2['_id'])->click();
        $this->wait();
        $this->byId('btn-messageDriver')->click();
        $this->wait();


        // Assert that previous messages in conversation are displayed
        foreach ($this->conversationSet() as $key => $message) {
            if($key > 3)
                $this->assertMessageSeenOnMessageModal($message);
        }

        $this->type($message_text, '#message_text');
        $this->byId('btn-save-messageDriver')->click();
        $this->wait();

        // Assert that message modal stays open
        $this->assertTrue($this->byId("messageDriverModal")->displayed());

        $dbMsg1 = $this->waitForDbUpdate($driver1, $org, $message_text, Message::STATUS_QUEUED);
        $id1 = $dbMsg1['_id'];
        $dbMsg2 = $this->waitForDbUpdate($driver2, $org, $message_text, Message::STATUS_QUEUED);
        $id2 = $dbMsg2['_id'];

        // Assert that the queued message is displayed in the message modal with status queued
        $this->assertMessageSeenOnMessageModal($dbMsg1);
        $this->assertMessageSeenOnMessageModal($dbMsg2);


        $sid1 = $dbMsg1['sid'];
        $sid2 = $dbMsg2['sid'];

        $this->wait();

        $this->postStatusUpdate($twilioUser, $dbOrg, $sid1, Message::STATUS_SENT, $driver1['mobile_phone_number']);
        $dbMsg1 = $this->waitForDbUpdate($driver1, $org, $message_text, Message::STATUS_SENT);
        $this->postStatusUpdate($twilioUser, $dbOrg, $sid2, Message::STATUS_SENT, $driver2['mobile_phone_number']);
        $dbMsg2 = $this->waitForDbUpdate($driver2, $org, $message_text, Message::STATUS_SENT);

        $this->wait();

        $this->assertMessageSeenOnMessageModal($dbMsg1);
        $this->assertMessageSeenOnMessageModal($dbMsg2);

        $this->postStatusUpdate($twilioUser, $dbOrg, $sid1, Message::STATUS_DELIVERED, $driver1['mobile_phone_number']);
        $dbMsg1 = $this->waitForDbUpdate($driver1, $org, $message_text, Message::STATUS_DELIVERED);
        $this->postStatusUpdate($twilioUser, $dbOrg, $sid2, Message::STATUS_DELIVERED, $driver2['mobile_phone_number']);
        $dbMsg2 = $this->waitForDbUpdate($driver2, $org, $message_text, Message::STATUS_DELIVERED);

        $this->wait();

        $this->assertMessageSeenOnMessageModal($dbMsg1);
        $this->assertMessageSeenOnMessageModal($dbMsg2);

        $this->postMessageToIncomingController(
            $twilioUser,
            $dbOrg,
            $org,
            $driver1['mobile_phone_number'],
            $reply_message_text);
        $this->postMessageToIncomingController(
            $twilioUser,
            $dbOrg,
            $org,
            $driver2['mobile_phone_number'],
            $reply_message_text);

        $dbMsg1 = $this->waitForDbUpdate($driver1, $org, $reply_message_text, Message::STATUS_RECEIVED);
        $dbMsg2 = $this->waitForDbUpdate($driver2, $org, $reply_message_text, Message::STATUS_RECEIVED);

        $this->wait();

        $this->assertMessageSeenOnMessageModal($dbMsg1);
        $this->assertMessageSeenOnMessageModal($dbMsg2);

        $this->wait();
        $this->byCssSelector('#messageDriverModal .modal-header button.close')->click();
        $this->wait();

        $this->byId('message' . $id1 )->click();
        $this->byId('message' . $id2 )->click();

        $this->wait();

        $id1 = $dbMsg1['_id'];
        $id2 = $dbMsg2['_id'];

        $this->byId('message' . $id1 )->click();
        $this->byId('message' . $id2 )->click();
        
        $this->byId('btn-delete-messages')->click();
        $this->wait();

    }

    /**
     * @param $driver
     * @param $org
     * @param $message_text
     * @param $status
     * @return array|null
     */
    protected function waitForDbUpdate($driver, $org, $message_text, $status)
    {
        $maxCnt = 10;
        $dbMsg = null;
        while ($maxCnt > 0) {
            $dbMsg = $this->getMongoConnection()->collection('messages')->findOne(
                [
                    'driver_id' => $driver['_id'],
                    'organisation_id' => $org['_id'],
                    'message_text' => $message_text,
                    'status' => $status
                ]);
            if (!is_null($dbMsg)) {
                break;
            }
            $this->wait();
            --$maxCnt;
        }
        $this->assertGreaterThan(0, $maxCnt, 'timed out waiting for database to update');
        return $dbMsg;
    }

    /**
     * @param $message
     */
    protected function assertMessageSeenOnMessageModal($message)
    {
        $statusText = ['queued'=>'Queued for','sent'=>'Sent to','delivered'=>'Delivered to','received'=>'Received from'];

        $actual = $this
            ->byCssSelector('#conversation_message' . $message['_id'] . ' .message_text')->text();
        $this
            ->assertThat($actual
                , $this
                ->equalTo($message['message_text']),'message text');
        $this
            ->assertThat(explode(' ', $this
                ->byCssSelector('#conversation_message' . $message['_id'] . ' .message_container')
                ->attribute('class')), $this
                ->contains($message['status']),'status css class');
        $this
            ->assertThat($this
                ->byCssSelector('#conversation_message' . $message['_id'] . ' .status')
                ->text(),$this
                ->equalTo($statusText[$message['status']]));

        // assert that a date is shown
        $this
            ->assertThat($this
                ->byCssSelector('#conversation_message' . $message['_id'] . ' .datetime')
                ->text(), $this
                ->logicalNot($this
                    ->isEmpty()),'datetime');
        
    }

}
