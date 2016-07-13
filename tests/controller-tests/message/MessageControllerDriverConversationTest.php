<?php
/**
 *
 * @version 0.0.1: MessageControllerDriverConversationTest.php 6/07/2016T09:34
 * @author Clifton IT Foundries Pty Ltd
 * @link http://cliftonwebfoundry.com.au
 * @copyright Copyright (c) 2016 Clifton IT Foundries Pty Ltd. All rights Reserved
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 *
 **/


namespace TruckerTracker;
use Faker\Provider\DateTime;

require_once __DIR__ . '/MessageControllerTestCase.php';


class MessageControllerDriverConversationTest extends MessageControllerTestCase
{


    protected $conversationSet;

    /**
     * @return array
     */
    protected function getFixture()
    {
        $this->conversationSet = $this->conversationSet();
        return [
            'users' =>              [],
            'password_resets' =>    [],
            'organisations' =>      $this->orgSet,
            'drivers' =>            $this->driverSet,
            'vehicles' =>           [],
            'messages' =>           $this->conversationSet,
            'locations' =>          []
        ];
    }

    /**
     *
     * get an conversation - a set of messages with one driver
     *
     * @test
     */
    public function gets_a_list_of_messages_for_one_driver()
    {
        // Arrange
        $org = $this->orgSet[0];
        $user = $this->user();
        $driver = $this->driverSet[0];

        // Act
        $this->actingAs($user)->get('/driver/'.$driver['_id'].'/conversation');

        // Assert
        $this->assertResponseOk();
        $this->seeJsonContains([
            '_id' => $driver['_id'],
            'first_name' => $driver['first_name'],
            'last_name' => $driver['last_name'],
            'mobile_phone_number' => $driver['mobile_phone_number'],
            'drivers_licence_number' => $driver['drivers_licence_number']
            ]);

        foreach ($this->conversationSet as $key => $message){
            if (! in_array($key,[4])) {
                $status = $message['status'];
                $status_at_name = $message['status'] . '_at';
                $expected_status_at_value = $message[$status_at_name];
                $this->seeJsonContains([
                    'message_text' => $message['message_text'],
                    'status' => $status,
                    $status_at_name => $expected_status_at_value
                ]);
            }
        }
/*
 * Unable to find JSON fragment [
 * "delivered_at":"2016-07-13T17:59:46+10:00"] within [{"_id":"110001","drivers_licence_number":"9841YG","first_name":"Driver","last_name":"One","messages":[{"_id":"ffffffffff02","message_text":"Good morning","received_at":"2016-07-13T18:00:45+10:00","status":"received"},{"_id":"ffffffffff04","message_text":"TRAVELLING","received_at":"2016-07-13T18:13:45+10:00","status":"received"},{"_id":"ffffffffff06","message_text":"THERE","received_at":"2016-07-13T18:33:45+10:00","status":"received"},{"_id":"ffffffffff08","message_text":"?","queued_at":"2016-07-13T18:37:45+10:00","sent_at":"2016-07-13T18:38:45+10:00","status":"sent"},{"_id":"ffffffffff01","delivered_at":"2016-07-13T17:59:45+10:00","message_text":"hello","queued_at":"2016-07-13T17:57:45+10:00","sent_at":"2016-07-13T17:58:45+10:00","status":"delivered"},{"_id":"ffffffffff03","delivered_at":"2016-07-13T18:03:45+10:00","message_text":"busy day today. Please proceed to point A and collect box X and drop off to address Y","queued_at":"2016-07-13T18:01:45+10:00","sent_at":"2016-07-13T18:02:45+10:00","status":"delivered"},{"_id":"ffffffffff07",
 * "delivered_at":"2016-07-13T18:36:45+10:00","message_text":"Great, now when you're done there please proceed to point B and collect box Y and drop off to address Z","queued_at":"2016-07-13T18:34:45+10:00","sent_at":"2016-07-13T18:35:45+10:00","status":"delivered"}],"mobile_phone_number":"+61419140683","organisation":{"_id":"10001","auto_reply":true,"first_user":null,"hour12":false,"name":"McSweeney Transport Group","timezone":"Australia\/Sydney","twilio_account_sid":"AC392e8d8bc564eb45ea67cc0f3a8ebf3c","twilio_auth_token":"36c8ee5499df1e116aa53b1ee05ca5fa","twilio_phone_number":"+15005550006","twilio_user":null,"twilio_user_password":"mstgpwd1"}}].
 * {"_id":"110001","first_name":"Driver","last_name":"One","mobile_phone_number":"+61419140683","drivers_licence_number":"9841YG","organisation":{"_id":"10001","name":"McSweeney Transport Group","timezone":"Australia\/Sydney","hour12":false,"twilio_account_sid":"AC392e8d8bc564eb45ea67cc0f3a8ebf3c","twilio_auth_token":"36c8ee5499df1e116aa53b1ee05ca5fa","twilio_phone_number":"+15005550006","twilio_user_password":"mstgpwd1","auto_reply":true,"first_user":null,"twilio_user":null},"messages":
 * [
 * {
 * "_id":"ffffffffff01","message_text":"hello",
 * "queued_at":"2016-07-13T18:11:01+10:00",
 * "sent_at":"2016-07-13T18:12:01+10:00",
 * "delivered_at":"2016-07-13T18:13:01+10:00",
 * "status":"delivered"
 * },
 * {
 * "_id":"ffffffffff02","message_text":"Good morning","received_at":"2016-07-13T18:14:01+10:00","status":"received"},{"_id":"ffffffffff03","message_text":"busy day today. Please proceed to point A and collect box X and drop off to address Y","queued_at":"2016-07-13T18:15:01+10:00","sent_at":"2016-07-13T18:16:01+10:00","delivered_at":"2016-07-13T18:17:01+10:00","status":"delivered"},{"_id":"ffffffffff04","message_text":"TRAVELLING","received_at":"2016-07-13T18:27:01+10:00","status":"received"},{"_id":"ffffffffff06","message_text":"THERE","received_at":"2016-07-13T18:47:01+10:00","status":"received"},{"_id":"ffffffffff07","message_text":"Great, now when you're done there please proceed to point B and collect box Y and drop off to address Z","queued_at":"2016-07-13T18:48:01+10:00","sent_at":"2016-07-13T18:49:01+10:00","delivered_at":"2016-07-13T18:50:01+10:00","status":"delivered"},{"_id":"ffffffffff08","message_text":"?","queued_at":"2016-07-13T18:51:01+10:00","sent_at":"2016-07-13T18:52:01+10:00","status":"sent"}]}
 */
    }
}
