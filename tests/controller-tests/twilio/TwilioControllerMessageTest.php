<?php

namespace TruckerTracker;

use TruckerTracker\Twilio\TwilioInterface;

require_once __DIR__ . '/TwilioControllerTestCase.php';

class TwilioControllerMessageTest extends TwilioControllerTestCase
{


    /**
     * @before
     */
    public function setUp()
    {
        parent::setUp();
    }

    /**
     * Post message.
     *
     * @return void
     *
     * @test
     */
    public function postMessageAsFirstUserRouteSuccess()
    {

        // Arrange
        $user = $this->firstUser();
        $twilioUser = $this->twilioUser();
        $org = $this->orgset[0];
        $expectedMessageText = 'hello';
        $driver = $this->driverset[0];
        $expectedStatus = 'queued';
        $this->injectMockTwilio($org, $driver['mobile_phone_number'], $twilioUser->username, $expectedMessageText, $expectedStatus);

        // Act
        // Assert
        $this->actAssertMessageSentToTwilio($user, $driver, $expectedMessageText, $org, $expectedStatus);

    }

    /**
     * Post message as operations user is ok
     *
     * @return void
     *
     * @test
     */
    public function postMessageAsOpsUserRouteSuccess()
    {

        // Arrange
        $user = $this->user();
        $twilioUser = $this->twilioUser();
        $org = $this->orgset[0];
        $driver = $this->driverset[0];
        $expectedMessageText = 'hello';
        $expectedStatus = 'queued';
        $this->injectMockTwilio($org, $driver['mobile_phone_number'], $twilioUser->username, $expectedMessageText, $expectedStatus);

        // Act
        // Assert
        $this->actAssertMessageSentToTwilio($user, $driver, $expectedMessageText, $org, $expectedStatus);
    }

    /**
     * Post message as Twilio user fails unauthorised
     *
     * @return void
     *
     * @test
     */
    public function postMessageAsTwilioUserFailsUnauthorised()
    {

        // Arrange
        $user = $this->twilioUser();
        $driver = $this->driverset[0];
        $messageText = 'hello';
        $this->injectMockNeverUsedTwilio();
        $expectedResponseCode = 403;

        // Act
        $this->actingAs($user)->json('post', '/driver/' . $driver['_id'].'/message',
            [
                'message_text' => $messageText
            ]);

        // Assert
        $this->assertResponseStatus($expectedResponseCode);
    }

    /**
     * @param $user
     * @param $driver
     * @param $expectedMessageText
     * @param $org
     * @param $expectedStatus
     * @return mixed
     */
    protected function actAssertMessageSentToTwilio($user, $driver, $expectedMessageText, $org, $expectedStatus)
    {
// Act

        $this->actingAs($user)->json('post', '/driver/' . $driver['_id']. '/message',
            [
                'message_text' => $expectedMessageText
            ]);

        // Assert
        $this->assertResponseOk();
        $this->seeJsonStructure(['_id', 'message_text', 'driver_id', 'organisation_id', 'queued_at', 'status', 'driver' => ['first_name', 'last_name']]);
        $this->seeJson(
            [
                'driver_id' => $driver['_id'],
                'organisation_id' => $org['_id'],
                'message_text' => $expectedMessageText,
                'status' => $expectedStatus
            ]);
        $data = json_decode($this->response->getContent(), true);
        $this->seeInDatabase('messages', [
            '_id' => $data['_id'],
            'driver_id' => $driver['_id'],
            'organisation_id' => $org['_id'],
            'message_text' => $expectedMessageText,
            'status' => $expectedStatus,
            'sid' => $data['sid'],
            'account_sid' => $data['account_sid']
        ]);
        return $data;
    }


}
