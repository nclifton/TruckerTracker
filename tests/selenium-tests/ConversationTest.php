<?php

namespace TruckerTracker;

Require_once __DIR__ . '/IntegratedTestCase.php';


class ConversationTest extends IntegratedTestCase
{

    protected function getFixture()
    {

        return [
            'users' =>              $this->fixtureUserset,
            'password_resets' =>    [],
            'organisations' =>      $this->orgSet,
            'drivers' =>            $this->driverSet,
            'vehicles' =>           [],
            'messages' =>           $this->conversationSet,
            'locations' =>          []
        ];
    }

    /**
     * @test
     */
    public function displays_conversation()
    {

        // Arrange
        $driver = $this->driverSet[0];
        $org = $this->orgSet[0];

        $dbOrg = $this->connection->collection('organisations')->findOne(['_id' => $org['_id']]);
        $twilioUser = $this->connection->collection('users')->findOne(['_id' => $dbOrg['twilio_user_id']]);
        $message_text = 'Hello';
        $reply_message_text = 'Ahoy hoy!';

        // Act
        $this->login();
        $this->byCssSelector('a[href="#message_drivers_collapsible"]')->click();
        $this->wait();
        $this->byId('driver' . $driver['_id'])->click();
        $this->wait();
        $this->byId('btn-messageDriver')->click();
        $this->wait();


        // Assert that previous messages in conversation are displayed
        foreach ($this->conversationSet as $key => $message) {
            if (!in_array($key,[4,7]))
                $this->assertMessageSeenOnMessageModal($message);
        }

        $this->type($message_text, '#message_text');
        $this->byId('btn-save-messageDriver')->click();
        $this->wait();

        // Assert that message modal stays open
        $this->assertTrue($this->byId("messageDriverModal")->displayed());

        $dbMsg = $this->waitForDbUpdate($driver, $org, $message_text, Message::STATUS_QUEUED);
        $id = $dbMsg['_id'];

        // Assert that the queued message is displayed in the message modal with status queued
        $this->assertMessageSeenOnMessageModal($dbMsg);

        $sid = $dbMsg['sid'];

        $this->wait();

        $this->postStatusUpdate($twilioUser, $dbOrg, $sid, Message::STATUS_SENT, $driver['mobile_phone_number']);
        $dbMsg = $this->waitForDbUpdate($driver, $org, $message_text, Message::STATUS_SENT);

        $this->wait();

        $this->assertMessageSeenOnMessageModal($dbMsg);

        $this->postStatusUpdate($twilioUser, $dbOrg, $sid, Message::STATUS_DELIVERED, $driver['mobile_phone_number']);
        $dbMsg = $this->waitForDbUpdate($driver, $org, $message_text, Message::STATUS_DELIVERED);

        $this->wait();

        $this->assertMessageSeenOnMessageModal($dbMsg);

        $this->postMessageToIncomingController(
            $twilioUser,
            $dbOrg,
            $org,
            $driver['mobile_phone_number'],
            $reply_message_text);

        $dbMsg = $this->waitForDbUpdate($driver, $org, $reply_message_text, Message::STATUS_RECEIVED);

        $this->wait();

        $this->assertMessageSeenOnMessageModal($dbMsg);

        $this->wait();
        $this->byCssSelector('#messageDriverModal .modal-header button.close')->click();
        $this->wait();

        $this->byId('message' . $id )->click();
        $this->wait();

        $id = $dbMsg['_id'];

        $this->byId('message' . $id )->click();
        
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
        $this->assertTrue($this->byId('conversation_message' . $message['_id'])->displayed());
        $this
            ->assertThat($this
                ->byCssSelector('#conversation_message' . $message['_id'] . ' .message_text')
                ->text(), $this
                ->equalTo($message['message_text']));
        $this
            ->assertThat(explode(' ', $this
                ->byCssSelector('#conversation_message' . $message['_id'] . ' .message_text')
                ->attribute('class')), $this
                ->contains($message['status']));
    }

}
