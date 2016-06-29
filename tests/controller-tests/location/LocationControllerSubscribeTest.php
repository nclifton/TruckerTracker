<?php

declare(ticks = 1);

namespace TruckerTracker;

require_once __DIR__ . '/LocationControllerTestCase.php';

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class LocationControllerSubscribeTest extends LocationControllerTestCase
{
    protected $locSet;
    protected $tempFile = __DIR__ . '/test-content.txt';

    /**
     * @before
     */
    public function setUp()
    {
        parent::setUp();
    }

    protected function getFixture()
    {
        $this->locSet = $this->locationSet;
        unset($this->locSet[0]['latitude']);
        unset($this->locSet[0]['longitude']);
        unset($this->locSet[0]['course']);
        unset($this->locSet[0]['speed']);
        unset($this->locSet[0]['datetime']);
        $this->locSet[0]['status'] = 'queued';
        $this->locSet[] = $this->locSet[0];
        unset($this->locSet[0]['sent_at']);
        unset($this->locSet[0]['delivered_at']);
        $this->locSet[1]['_id'] = '300002';
        $this->locSet[1]['sid'] = '3333333';
        $this->locSet[1]['status'] = 'sent';
        $this->locSet[1]['queued_at'] = (new \DateTime($this->locSet[1]['queued_at']))->add(new \DateInterval('P2M'))->format('c');
        $this->locSet[1]['sent_at'] =   (new \DateTime($this->locSet[1]['queued_at']))->add(new \DateInterval('P2M'))->format('c');
        $this->locSet[] = $this->locSet[1];
        $this->locSet[2]['_id'] = '300003';
        $this->locSet[2]['sid'] = '4444444';
        $this->locSet[2]['status'] = 'delivered';
        $this->locSet[2]['queued_at'] = (new \DateTime($this->locSet[2]['queued_at']))->add(new \DateInterval('P2M'))->format('c');
        $this->locSet[2]['sent_at'] = (new \DateTime($this->locSet[2]['sent_at']))->add(new \DateInterval('P2M'))->format('c');
        $this->locSet[2]['delivered_at'] = (new \DateTime($this->locSet[2]['sent_at']))->add(new \DateInterval('P2M'))->format('c');

        return [
            'users' => [],
            'password_resets' => [],
            'organisations' => $this->orgset,
            'drivers' => [],
            'vehicles' => $this->vehicleset,
            'messages' => [],
            'locations' => $this->locSet
        ];
    }

    /**
     * get location message status sent update
     *
     * @test
     */
    public function subscribes_and_receives_location_status_sent_update()
    {
        // Arrange
        $org = $this->orgset[0];
        $user = $this->user();
        $tUser = $this->twilioUser();
        $loc = $this->locSet[0];
        $vehicle = $this->vehicleset[0];
        $sentMsg = [
            'MessageSid' => $loc['sid'],
            'SmsSid' => $loc['sid'],
            'AccountSid' => $org['twilio_account_sid'],
            'MessagingServiceSid' => 'MG123456789012345678901234',
            'MessageStatus' => 'sent',
            'To' => $vehicle['mobile_phone_number'],
            'From' => $org['twilio_phone_number']
        ];
        $expectedUpdate1 = [
            '_id' => $loc['_id'],
            'status' => $sentMsg['MessageStatus'],
            'sid' => $loc['sid']
        ];
        $expectedVehicle = $vehicle;
        unset($expectedVehicle['organisation_id']);
        unset($expectedVehicle['tracker_password']);

        // Act
        $response = $this->actingAs($user)->action('get', 'LocationController@subscribe');
        // Assert
        $this->assertResponseOk();
        $this->assertNotNull($response);

        $content = $this->sendIncomingLocationMessageStatusGetSSEResponseContent($tUser, $sentMsg, $response);
        $this->assertNotEmpty($content);
        $this->assertJsonContains($content, $expectedUpdate1);
        $this->assertJsonContains($content, $expectedVehicle);
        $this->seeJsonStructure(['sent_at'],(array) json_decode($content));


    }

    /**
     * get location data
     *
     * @test
     */
    public function subscribes_and_receives_location_status_delivered_update()
    {
        // Arrange
        $org = $this->orgset[0];
        $user = $this->user();
        $tUser = $this->twilioUser();
        $loc = $this->locSet[1];
        $vehicle = $this->vehicleset[0];
        $deliveredMsg = [
            'MessageSid' => $loc['sid'],
            'SmsSid' => $loc['sid'],
            'AccountSid' => $org['twilio_account_sid'],
            'MessagingServiceSid' => 'MG123456789012345678901234',
            'MessageStatus' => 'delivered',
            'To' => $vehicle['mobile_phone_number'],
            'From' => $org['twilio_phone_number']
        ];
        $expectedUpdate2 = [
            '_id' => $loc['_id'],
            'status' => $deliveredMsg['MessageStatus'],
            'sid' => $loc['sid']
        ];
        $expectedVehicle = $vehicle;
        unset($expectedVehicle['organisation_id']);
        unset($expectedVehicle['tracker_password']);

        // Act
        $response = $this->actingAs($user)->action('get', 'LocationController@subscribe');
        // Assert
        $this->assertResponseOk();
        $this->assertNotNull($response);

        $content = $this->sendIncomingLocationMessageStatusGetSSEResponseContent($tUser, $deliveredMsg, $response);
        $this->assertNotEmpty($content);
        $this->assertJsonContains($content, $expectedUpdate2);
        $this->assertJsonContains($content, $expectedVehicle);
        $this->seeJsonStructure(['delivered_at'],(array) json_decode($content));



    }

    /**
     * get location data
     *
     * @test
     */
    public function subscribes_and_receives_location_information_update()
    {
        // Arrange
        $org = $this->orgset[0];
        $user = $this->user();
        $tUser = $this->twilioUser();
        $loc = $this->locSet[0];
        $vehicle = $this->vehicleset[0];
        $locMsg = [
            'MessageSid' => '999999',
            'SmsSid' => '999999',
            'AccountSid' => $org['twilio_account_sid'],
            'MessagingServiceSid' => 'MG123456789012345678901234',
            'From' => $vehicle['mobile_phone_number'],
            'To' => $org['twilio_phone_number'],
            'Body' => 'Lat:S34.04387,Lon:E150.84342419999996,Course:0.00,Speed:0.5204,DateTime:16 -07 -02 21:05:43',
            'NumMedia' => '0'
        ];
        $expectedLocDb = [
            'sid_response' => $locMsg['MessageSid'],
            'latitude' => -34.04387,
            'longitude' => 150.84342419999996,
            'course' => 0.00,
            'speed' => 0.5204,
            'datetime' => new \DateTime('2016-07-02T21:05:43+10:00'),
            'status' => 'received'
        ];
        $expectedLoc = array_merge($loc,$expectedLocDb);
        unset($expectedLoc['sid']);
        unset($expectedLoc['sid_response']);
        unset($expectedLoc['organisation_id']);
        unset($expectedLoc['vehicle_id']);
        $expectedLoc['datetime'] = $expectedLoc['datetime']->format($org['datetime_format']);
        $expectedLoc['queued_at'] = (new \DateTime($expectedLoc['queued_at']))->format($org['datetime_format']);
        //$expectedLoc['sent_at'] = $expectedLoc['sent_at']->format($org['datetime_format']);
        //$expectedLoc['delivered_at'] = $expectedLoc['delivered_at']->format($org['datetime_format']);
        $expectedVehicle = $vehicle;
        unset($expectedVehicle['organisation_id']);
        unset($expectedVehicle['tracker_password']);

        // Act
        $response = $this->actingAs($user)->action('get', 'LocationController@subscribe');
        // Assert
        $this->assertResponseOk();
        $this->assertNotNull($response);

        $content = $this->sendIncomingLocationMessageGetSSEResponseContent($tUser, $locMsg, $response);
        $this->assertNotEmpty($content);

        $this->assertJsonContains($content, $expectedLoc);
        $this->assertJsonContains($content, $expectedVehicle);
        $this->seeJsonStructure(['received_at'],(array) json_decode($content));

        $this->seeInDatabase('locations',$expectedLocDb);


    }


    /**
     * @after
     */
    public function tearDown()
    {
        if (file_exists($this->tempFile)) {
            delete($this->tempFile);
        }
    }

    /**
     * @param $content
     * @param $expectedUpdate1
     */
    protected function assertJsonContains($content, $expectedUpdate1)
    {
        $actual = json_encode(Arr::sortRecursive((array)json_decode($content)));

        foreach (Arr::sortRecursive($expectedUpdate1) as $key => $value) {
            $expected = $this->formatToExpectedJson($key, $value);

            $this->assertTrue(
                Str::contains($actual, $expected),
                "Unable to find JSON fragment [{$expected}] within [{$actual}]."
            );
        }
    }

    /**
     * @param $twilioUser
     * @param $msg
     * @param $sseResponse
     * @return string
     */
    protected function sendIncomingLocationMessageStatusGetSSEResponseContent($twilioUser, $msg, $sseResponse)
    {
        return $this->forkToSendAndReceive($twilioUser, $msg, $sseResponse, '/incoming/message/status');
    }

    /**
     * @param $twilioUser
     * @param $msg
     * @param $sseResponse
     * @param $path
     * @return string
     */
    protected function forkToSendAndReceive($twilioUser, $msg, $sseResponse, $path)
    {
        $pid = pcntl_fork();
        if ($pid) {
            sleep(1); // time to allow the child to start reading the stream
            $this->actingAs($twilioUser)->json('POST', $path, $msg);
            $this->assertResponseOk();
            sleep(3); // give the child process time to finish and flush it's output buffer to file
            posix_kill($pid, SIGTERM);
            pcntl_waitpid($pid, $status, WUNTRACED);
        } else {
            ob_start(function ($contents) {
                file_put_contents($this->tempFile, $contents);
                return $contents;
            });
            $sseResponse->sendContent();
            exit;
        }
        $content = file_get_contents($this->tempFile);
        return $content;
    }

    private function sendIncomingLocationMessageGetSSEResponseContent($twilioUser, $msg, $sseResponse)
    {
        return $this->forkToSendAndReceive($twilioUser, $msg, $sseResponse, '/incoming/message');

    }


}
