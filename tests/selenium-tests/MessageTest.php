<?php

namespace TruckerTracker;

use Artisan;
use DB;

Require_once __DIR__ . '/IntegratedTestCase.php';


class MessageTest extends IntegratedTestCase
{
    protected function artisanSeedDb()
    {
        Artisan::call('db:seed', ['--class' => 'MessageTestDbSeeder']);
    }
    /**
     * @test
     */
    public function queues_message_and_delete()
    {

        // Arrange
        $driver = $this->driverSet[0];
        $org = $this->orgSet[0];

        $dbOrg = DB::collection('organisations')->where(['_id' => $org['_id']])->first();
        $twilioUser = DB::collection('users')->where(['_id' => $dbOrg['twilio_user_id']])->first();
        $message_text = 'Hello';
        $reply_message_text = 'Ahoy hoy!';

        // Act
        $this->login();
        $this->byCssSelector('#accordion a[href="#message_drivers_collapsible"]')->click();
        $this->wait();
        $this->byId('driver' . $driver['_id'])->click();
        $this->wait();
        $this->byId('btn-messageDriver')->click();
        $this->wait();
        $this->byId('btn-save-messageDriver')->click();
        $this->wait();

        $this->assertThat($this->byId('messageDriverModalLabel')->text(), $this->equalTo('Message Driver'));
        $this->assertThat(explode(' ', $this->byCssSelector('#messageForm > div:first-child')->attribute('class')),
            $this->contains('has-error'));
        $this->assertThat($this->byCssSelector('#messageForm > div:first-child > div > span > strong')->text(),
            $this->equalTo('Sorry, you can\'t send nothing'));

        // closing the dialog clears the error display
        $this->byCssSelector('#messageDriverModal button.close')->click();
        $this->wait();
        $this->byId('btn-messageDriver')->click();
        $this->wait();
        $this->assertThat(explode(' ', $this->byCssSelector('#messageForm > div:first-child')->attribute('class')),
            $this->logicalNot($this->contains('has-error')));

        $this->type($message_text, '#message_text');
        $this->wait();
        $this->byId('btn-save-messageDriver')->click();
        $this->wait();
        $this->byCssSelector('#messageDriverModal .modal-header button.close')->click();
        $this->wait();

        // Assert
        $dbMsg = $this->waitForDbUpdate($driver, $org, $message_text, Message::STATUS_QUEUED);

        $id = $dbMsg['_id'];
        $this->assertThat($this->byId('message' . $id)->displayed(), $this->isTrue());

        $sid = $dbMsg['sid'];
        $queued_at = $dbMsg['queued_at']->toDateTime()->setTimeZone(new \DateTimeZone('Australia/Sydney'));

        $this
            ->assertThat($this
                ->byCssSelector('#message' . $id . ' .first_name')
                ->text(), $this
                ->equalTo($driver['first_name']));
        $this
            ->assertThat($this
                ->byCssSelector('#message' . $id . ' .last_name')
                ->text(), $this
                ->equalTo($driver['last_name']));

        $this->assertMessageStatus($dbMsg, Message::STATUS_QUEUED, $queued_at, $org);

        $this
            ->assertThat($this
                ->byId('message' . $id)
                ->attribute('title'), $this
                ->equalTo($dbMsg['message_text']));

        $this->wait();

        $sent_at = new \DateTime();
        $this->postStatusUpdate($twilioUser, $dbOrg, $sid, Message::STATUS_SENT, $driver['mobile_phone_number']);
        $dbMsg = $this->waitForDbUpdate($driver, $org, $message_text, Message::STATUS_SENT);

        $this->wait();

        $this->assertMessageStatus($dbMsg, Message::STATUS_SENT, $sent_at, $org);


        $delivered_at = new \DateTime();
        $this->postStatusUpdate($twilioUser, $dbOrg, $sid, Message::STATUS_DELIVERED, $driver['mobile_phone_number']);
        $dbMsg = $this->waitForDbUpdate($driver, $org, $message_text, Message::STATUS_DELIVERED);

        $this->wait();

        $this->assertMessageStatus($dbMsg, Message::STATUS_DELIVERED, $delivered_at, $org);

        $received_at = new \DateTime();
        $this->postMessageToIncomingController(
            $twilioUser,
            $dbOrg,
            $org,
            $driver['mobile_phone_number'],
            $reply_message_text);

        $dbMsg = $this->waitForDbUpdate($driver, $org, $reply_message_text, Message::STATUS_RECEIVED);

        $this->wait();

        $this->assertMessageStatus($dbMsg, Message::STATUS_RECEIVED, $received_at, $org);

        // test delete
        $this->byId('message' . $id )->click();
        $this->byId('btn-delete-messages')->click();
        $this->wait();

        $this->notSeeId('message' . $id);

        $this->notSeeInDatabase('messages', ['_id' => $id]);

        $id = $dbMsg['_id'];

        $this->assertEquals('true',$this->byId('btn-delete-messages')->attribute('disabled'));

        $this->byId('message' . $id )->click();
        $this->byId('btn-delete-messages')->click();
        $this->wait();

        $this->notSeeId('message' . $id);

        $this->notSeeInDatabase('messages', ['_id' => $id]);

        $this->assertEquals('true',$this->byId('btn-delete-messages')->attribute('disabled'));


    }

    /**
     * @param $message
     * @param $status
     * @param $expectedDatetime
     * @param $org
     */
    protected function assertMessageStatus($message, $status, $expectedDatetime, $org)
    {
        $this
            ->assertThat($this
                ->byCssSelector('#message' . $message['_id'] . ' .status')
                ->text(), $this
                ->equalTo($status));
        $expectedTimeStamp = $expectedDatetime->getTimeStamp();
        $actualTimeStamp = (new \DateTime($this
            ->byCssSelector('#message' . $message['_id'] . ' .status_at')
            ->text()))
            ->getTimestamp();

        $this
            ->assertThat($actualTimeStamp, $this
                ->equalTo($expectedTimeStamp, 10));
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
            $dbMsg = DB::collection('messages')->where(
                [
                    'driver_id' => $driver['_id'],
                    'organisation_id' => $org['_id'],
                    'message_text' => $message_text,
                    'status' => $status
                ])->first();
            if (!is_null($dbMsg)) {
                break;
            }
            $this->wait();
            --$maxCnt;
        }
        $this->assertGreaterThan(0, $maxCnt, 'timed out waiting for database to update');
        return $dbMsg;
    }

}
