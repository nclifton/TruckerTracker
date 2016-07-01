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

use DB;
use Faker\Provider\DateTime;
use Guzzle;
use Symfony\Component\HttpFoundation\StreamedResponse;
use TruckerTracker\Events\LocationUpdate;
use TruckerTracker\Http\Controllers\LocationController;
use TruckerTracker\Http\Controllers\TwilioIncomingController;

require_once __DIR__ . '/TwilioControllerTestCase.php';

class TwilioControllerIncomingTest extends TwilioControllerTestCase
{
    protected $datetime_format;
    protected $allowedDateTimeDiffInSeconds = 5;

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
            'organisations' => $this->orgSet,
            'drivers' => $this->driverSet,
            'vehicles' => $this->vehicleSet,
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
    public function update_message_status()
    {

        // Arrange
        $user= $this->twilioUser();
        $message = $this->messageSet[0];
        $driver = $this->driverSet[0];
        $org = $this->orgSet[0];
        $this->datetime_format = $org['datetime_format'];
        $expectedUrl = 'http://homestead.app/pub/messages'.$org['_id'];
        $expectedMessage = $message;
        $expectedMessage['status']='delivered';
        unset($expectedMessage['organisation_id']);
        unset($expectedMessage['driver_id']);
        unset($expectedMessage['sid']);
        $expectedMessage['delivered_at'] = (new \DateTime())->format($org['datetime_format']);
        $expectedMessage['queued_at'] = (new \DateTime($expectedMessage['queued_at']))->format($org['datetime_format']);
        $expectedMessage['sent_at'] = (new \DateTime($expectedMessage['sent_at']))->format($org['datetime_format']);
        $expectedPostData = [
            'json' => [
                'event' => 'MessageUpdate',
                'data' => $expectedMessage
            ]
        ];
        $mockResponse = \Mockery::mock(\GuzzleHttp\Message\ResponseInterface::class);
        $mockResponse->shouldReceive('getStatusCode')->once()->andReturn(201);

        Guzzle::shouldReceive('post')->once()->with($expectedUrl,\Mockery::on(function($arg) use ($expectedPostData){
            $matches = $this->array_contains_recursive($expectedPostData, $arg);
            return $matches;
        }))->andReturn($mockResponse);

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
     * updates the status of a sent message from a request with the right account sid
     *
     * @test
     */
    public function update_location_status()
    {

        // Arrange
        $user= $this->twilioUser();
        $location = $this->locationSet[0];
        $vehicle = $this->vehicleSet[0];
        $org = $this->orgSet[0];
        $this->datetime_format = $org['datetime_format'];

        $expectedUrl = 'http://homestead.app/pub/locations'.$org['_id'];
        $expectedLocation = $location;
        $expectedLocation['status']='delivered';
        unset($expectedLocation['organisation_id']);
        unset($expectedLocation['vehicle_id']);
        unset($expectedLocation['sid']);
        $expectedLocation['delivered_at'] = (new \DateTime())->format($org['datetime_format']);
        $expectedLocation['queued_at'] = (new \DateTime($expectedLocation['queued_at']))->format($org['datetime_format']);
        $expectedLocation['sent_at'] = (new \DateTime($expectedLocation['sent_at']))->format($org['datetime_format']);
        $expectedPostData = [
            'json' => [
                'event' => 'LocationUpdate',
                'data' => $expectedLocation
            ]
        ];
        $mockResponse = \Mockery::mock(\GuzzleHttp\Message\ResponseInterface::class);
        $mockResponse->shouldReceive('getStatusCode')->once()->andReturn(201);

        Guzzle::shouldReceive('post')->once()->with($expectedUrl,\Mockery::on(function($arg) use ($expectedPostData){
            $matches = $this->array_contains_recursive($expectedPostData, $arg);
            return $matches;
        }))->andReturn($mockResponse);

        // Act
        $this->actingAs($user)->post( '/incoming/message/status',
            [
                'MessageSid' => $location['sid'],
                'AccountSid' => $org['twilio_account_sid'],
                'MessageStatus' => 'delivered',
                'To' => $vehicle['mobile_phone_number'],
                'From' => $org['twilio_phone_number']
            ]);

        // Assert
        $this->assertResponseOk();
        $this->seeInDatabase('locations',
            [
                '_id' => $location['_id'],
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
        $driver = $this->driverSet[0];
        $org = $this->orgSet[1];

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
        $org = $this->orgSet[0];
        $driver = $this->driverSet[0];
        $this->datetime_format = $org['datetime_format'];

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

        $expectedUrl = 'http://homestead.app/pub/messages'.$org['_id'];

        $expectedMessage['_id'] = '*';
        $expectedMessage['message_text'] = $message['Body'];
        $expectedDriver = $driver;
        unset($expectedDriver['organisation_id']);
        $expectedMessage['driver'] = $expectedDriver;
        $expectedMessage['status']='received';
        $expectedMessage['received_at'] = (new \DateTime())->format($org['datetime_format']);
        $expectedPostData = [
            'json' => [
                'event' => 'MessageReceived',
                'data' => $expectedMessage
            ]
        ];
        $mockResponse = \Mockery::mock(\GuzzleHttp\Message\ResponseInterface::class);
        $mockResponse->shouldReceive('getStatusCode')->once()->andReturn(201);

        Guzzle::shouldReceive('post')->once()->with($expectedUrl,\Mockery::on(function($arg) use ($expectedPostData){
            $matches = $this->array_contains_recursive($expectedPostData, $arg);
            return $matches;
        }))->andReturn($mockResponse);

        // Act
        $this->actingAs($user)->json('POST','/incoming/message',$message);

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
        $org = $this->orgSet[0];

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
        $org = $this->orgSet[0];

        DB::collection('organisations')->where('_id', $org['_id'])->update(['auto_reply'=>false]);

        $user= $this->twilioUser();
        $driver = $this->driverSet[0];
        $this->datetime_format = $org['datetime_format'];

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

        $expectedUrl = 'http://homestead.app/pub/messages'.$org['_id'];

        $expectedMessage['_id'] = '*';
        $expectedMessage['message_text'] = $message['Body'];
        $expectedDriver = $driver;
        unset($expectedDriver['organisation_id']);
        $expectedMessage['driver'] = $expectedDriver;
        $expectedMessage['status']='received';
        $expectedMessage['received_at'] = (new \DateTime())->format($org['datetime_format']);
        $expectedPostData = [
            'json' => [
                'event' => 'MessageReceived',
                'data' => $expectedMessage
            ]
        ];
        $mockResponse = \Mockery::mock(\GuzzleHttp\Message\ResponseInterface::class);
        $mockResponse->shouldReceive('getStatusCode')->once()->andReturn(201);

        Guzzle::shouldReceive('post')->once()->with($expectedUrl,\Mockery::on(function($arg) use ($expectedPostData){
            $matches = $this->array_contains_recursive($expectedPostData, $arg);
            return $matches;
        }))->andReturn($mockResponse);

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
     * Will accept a location message from a vehicle tracker and not auto reply and add to vehicle locations collection
     *
     * @test
     */
    public function acceptsVehicleLocationAddsToLocationsCollection(){

        // Arrange
        $user= $this->twilioUser();
        $org = $this->orgSet[0];
        $vehicle = $this->vehicleSet[0];
        $location = $this->locationSet[1];

        DB::collection('locations')->where('_id',$this->locationSet[0]['_id'])->delete();

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

        $expectedLocationDb =
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

        $this->datetime_format = $org['datetime_format'];

        $expectedUrl = 'http://homestead.app/pub/locations'.$org['_id'];
        $expectedData = $expectedLocationDb;
        unset($expectedData['sid_response']);
        unset($expectedData['vehicle_id']);
        unset($expectedData['sid']);
        $expectedData['received_at'] = (new \DateTime())->format($org['datetime_format']);
        $expectedData['delivered_at'] = (new \DateTime($location['delivered_at']))->format($org['datetime_format']);
        $expectedData['queued_at'] = (new \DateTime($location['queued_at']))->format($org['datetime_format']);
        $expectedData['sent_at'] = (new \DateTime($location['sent_at']))->format($org['datetime_format']);
        $expectedData['datetime'] = $expectedData['datetime']->format($org['datetime_format']);
        $expectedPostData = [
            'json' => [
                'event' => 'LocationReceived',
                'data' => $expectedData
            ]
        ];
        $mockResponse = \Mockery::mock(\GuzzleHttp\Message\ResponseInterface::class);
        $mockResponse->shouldReceive('getStatusCode')->once()->andReturn(201);

        Guzzle::shouldReceive('post')->once()->with($expectedUrl,\Mockery::on(function($arg) use ($expectedPostData){
            $matches = $this->array_contains_recursive($expectedPostData, $arg);
            return $matches;
        }))->andReturn($mockResponse);

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
        $this->seeInDatabase('locations', $expectedLocationDb);

    }

    private function array_contains_recursive($expected, $actual)
    {
        foreach ($expected as $key => $value) {
            if (key_exists($key, $actual)) {
                if (is_array($value) != is_array($actual[$key])) {
                    return false;
                } elseif (is_array($value) && is_array($actual[$key])) {
                    if (!$this->array_contains_recursive($value, $actual[$key])) {
                        return false;
                    }
                } elseif ($this->isDate($value) && $this->isDate($actual[$key])){
                    if (!$this->datesAreClose($value,$actual[$key])) {
                        return false;
                    }
                } elseif ($value != '*' && $value != $actual[$key]){
                    return false;
                }
            } else {
                return false;
            }
        }
        return true;
    }

    private function isDate($value)
    {
        $dt = \DateTime::createFromFormat($this->datetime_format,$value);
        return !($dt === false);

    }

    private function datesAreClose($expected, $actual)
    {
        $dt1 = \DateTime::createFromFormat($this->datetime_format,$expected);
        $dt2 =  \DateTime::createFromFormat($this->datetime_format,$expected);
        $timestamp = $dt1->getTimestamp();
        $timestamp1 = $dt2->getTimestamp();
        $b = abs($timestamp - $timestamp1) < $this->allowedDateTimeDiffInSeconds;
        return ($b);


    }

}
