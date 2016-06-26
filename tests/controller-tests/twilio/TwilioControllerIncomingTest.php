<?php
/**
 *
 * @version 0.0.1: TestIncoming.php 27/05/2016T01:31
 * @author Clifton IT Foundries Pty Ltd
 * @link http://cliftonwebfoundry.com.au
 * @copyright Copyright (c) 2016 Clifton IT Foundries Pty Ltd. All rights Reserved
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 *
 **/
namespace TruckerTracker;

use Symfony\Component\HttpFoundation\StreamedResponse;
use TruckerTracker\Events\LocationUpdate;
use TruckerTracker\Http\Controllers\LocationController;
use TruckerTracker\Http\Controllers\TwilioIncomingController;

require_once __DIR__ . '/TwilioControllerTestCase.php';

class TwilioControllerIncomingTest extends TwilioControllerTestCase
{

    /**
     * @before
     */
    public function setUp()
    {
        parent::setUp();
    }

    protected function getFixture()
    {
        return [
            'users' => [],
            'password_resets' => [],
            'organisations' => $this->orgset,
            'drivers' => $this->driverset,
            'vehicles' => $this->vehicleset,
            'messages' => $this->messageSet,
            'locations' => $this->locationSet
        ];
    }

    /**
     * @test
     */
    public function incomingForVoiceAcceptsPostMethod()
    {
        // Arrange
        $user= $this->twilioUser();

        // Act
        $this->actingAs($user)->post('/incoming');

        // Assert
        $this->assertResponseOk();
        $this->seeElement('Say');

    }

    /**
     * updates the status of a sent message from a request with the right account sid
     * 
     * @test
     */
    public function updateMessageStatus()
    {

        // Arrange
        $user= $this->twilioUser();
        $message = $this->messageSet[0];
        $driver = $this->driverset[0];
        $org = $this->orgset[0];

        // Act
        $this->actingAs($user)->post( '/incoming/message/status',
            [
                'MessageSid' => $message['sid'],
                'AccountSid' => $org['twilio_account_sid'],
                'MessageStatus' => 'delivered',
                'To' => $driver['mobile_phone_number'],
                'From' => $org['twilio_phone_number']
            ]);

        // Assert
        $this->assertResponseOk();
        $this->seeInDatabase('messages',
            [
                '_id' => $message['_id'],
                'status' => 'delivered'
            ]);

    }
    /**
     * does not update the status of a sent message from a request with the wrong account sid
     *
     * @test
     */
    public function notUpdateMessageStatusWrongAcctSid()
    {

        // Arrange
        $user= $this->twilioUser();
        $message = $this->messageSet[0];
        $driver = $this->driverset[0];
        $org = $this->orgset[1];

        // Act
        $incomingMessage = [
            'MessageSid' => $message['sid'],
            'AccountSid' => $org['twilio_account_sid'],
            'MessageStatus' => 'delivered',
            'To' => $driver['mobile_phone_number'],
            'From' => $org['twilio_phone_number']
        ];
        $response = $this->actingAs($user)->call('post', '/incoming/message/status',
            $incomingMessage);

        // Assert
        $this->assertEquals(200,$response->status());
        $this->notSeeInDatabase('messages',
            [
                'sid' => $incomingMessage['MessageSid'],
                'status' => $incomingMessage['MessageStatus']
            ]);

    }

    /**
     * Will accept a message from a driver and auto replies
     * 
     * @test
     */
    public function acceptsMessageWithAutoReply()
    {

        // Arrange
        $user= $this->twilioUser();
        $org = $this->orgset[0];
        $driver = $this->driverset[0];
        
        $message = [
            'MessageSid' => '9999999',
            'SmsSid' => '9999999',
            'AccountSid' => $org['twilio_account_sid'],
            'MessagingServiceSid' => 'MG123456789012345678901234',
            'From' => $driver['mobile_phone_number'],
            'To' => $org['twilio_phone_number'],
            'Body' => 'Ahoy hoy!',
            'NumMedia' => '0'
        ];

        // Act
        $this->actingAs($user)->json('POST','/incoming/message',$message);
        $this->json('POST','/incoming/message',$message);

        // Assert
        $this->assertResponseOk();
        $this->seeElement('Response');
        $this->seeElement('Message');
        $this->seeInElement('Message','Thank you '.$driver['first_name'].', Message received');
        $this->seeInDatabase('messages',
            [
                'sid' => $message['MessageSid'],
                'driver_id' => $driver['_id'],
                'message_text' => $message['Body'],
                'status' => 'received'
            ]);
    }

    /**
     * Will NOT accept a message from an unknown number - no reply - not recorded
     *
     * @test
     */
    public function notAcceptMessageFromUnknownNumber()
    {

        // Arrange
        $user= $this->twilioUser();
        $org = $this->orgset[0];

        $message = [
            'MessageSid' => '9999999',
            'SmsSid' => '9999999',
            'AccountSid' => $org['twilio_account_sid'],
            'MessagingServiceSid' => 'MG123456789012345678901234',
            'From' => '+61234567890',
            'To' => $org['twilio_phone_number'],
            'Body' => 'Ahoy hoy!',
            'NumMedia' => '0'
        ];

        // Act
        $this->actingAs($user)->json('POST','/incoming/message',$message);

        // Assert
        $this->assertResponseOk();
        $this->seeElement('Response');
        try{
            $this->seeElement('Message');
            $this->fail('should not see Message');
        } catch (\Exception $e){

        }

       $this->notSeeInDatabase('messages',
            [
                'sid' => $message['MessageSid']
            ]);
    }


    /**
     * Will accept a message from a driver and not auto reply when no auto reply is set - message is recorded
     *
     * @test
     */
    public function acceptsMessageWithoutAutoReply()
    {

        // Arrange
        $user= $this->twilioUser();
        $org = $this->orgset[1];
        $driver = $this->driverset[0];

        $message = [
            'MessageSid' => '9999999',
            'SmsSid' => '9999999',
            'AccountSid' => $org['twilio_account_sid'],
            'MessagingServiceSid' => 'MG123456789012345678901234',
            'From' => $driver['mobile_phone_number'],
            'To' => $org['twilio_phone_number'],
            'Body' => 'Ahoy hoy!',
            'NumMedia' => '0'
        ];

        // Act
        $this->actingAs($user)->json('POST','/incoming/message',$message);

        // Assert
        $this->assertResponseOk();
        $this->seeElement('Response');
        try{
            $this->seeElement('Message');
            $this->fail('should not see Message');
        } catch (\Exception $e){

        }
        $this->seeInDatabase('messages',
            [
                'sid' => $message['MessageSid'],
                'driver_id' => $driver['_id'],
                'message_text' => $message['Body'],
                'status' => 'received'
            ]);
    }

    /**
     * Will accept a location message from a vehicle and not auto reply and add to vehicle locations collection
     *
     * @test
     */
    public function acceptsVehicleLocationNoAutoReplyAddsToLocationsCollection(){

        // Arrange
        $user= $this->twilioUser();
        $org = $this->orgset[1];
        $vehicle = $this->vehicleset[0];
        $location = $this->locationSet[0];
        $this->expectsEvents(LocationUpdate::class);

        $locMsg = [
            'MessageSid' => '9999999',
            'SmsSid' => '9999999',
            'AccountSid' => $org['twilio_account_sid'],
            'MessagingServiceSid' => 'MG123456789012345678901234',
            'From' => $vehicle['mobile_phone_number'],
            'To' => $org['twilio_phone_number'],
            'Body' => 'Lat:S34.04387,Lon:E150.84342419999996,Course:0.00,Speed:0.5204,DateTime:16 -07 -02 21:05:43',
            'NumMedia' => '0'
        ];

        $expectedLocation =
            [
                'sid' => $location['sid'],
                'sid_response' => $locMsg['MessageSid'],
                'vehicle_id' => $vehicle['_id'],
                'latitude' => -34.04387,
                'longitude' => 150.84342419999996,
                'course' => 0.00,
                'speed' => 0.5204,
                'datetime' => new \DateTime('2016-07-02T21:05:43+10:00'),
                'status' => 'received'
            ];

        // Act
        $this->actingAs($user)->json('POST','/incoming/message',$locMsg);

        // Assert
        $this->assertResponseOk();
        $this->seeElement('Response');
        try{
            $this->seeElement('Message');
            $this->fail('should not see Message in response');
        } catch (\Exception $e){

        }
        $this->notSeeInDatabase('messages',
            [
                'sid' => $locMsg['MessageSid']
            ]);
        $this->seeInDatabase('locations', $expectedLocation);

    }



}
