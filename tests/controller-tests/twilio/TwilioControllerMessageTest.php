<?php

namespace TruckerTracker;

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
        $org = $this->orgSet[0];
        $expectedMessageText = 'hello';
        $driver = $this->driverSet[0];
        $expectedStatus = 'queued';
        $this->mockTwilio($org, $driver['mobile_phone_number'], $twilioUser->username, $expectedMessageText, $expectedStatus);

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
        $org = $this->orgSet[0];
        $driver = $this->driverSet[0];
        $expectedMessageText = 'hello';
        $expectedStatus = 'queued';
        $this->mockTwilio($org, $driver['mobile_phone_number'], $twilioUser->username, $expectedMessageText, $expectedStatus);

        // Act
        // Assert
        $this->actAssertMessageSentToTwilio($user, $driver, $expectedMessageText, $org, $expectedStatus);
    }

    /**
     * Post message validates that message is not blank - fails
     *
     * @return void
     *
     * @test
     */
    public function postMessageEmptyMessageFails422()
    {

        // Arrange
        $user = $this->user();
        $driver = $this->driverSet[0];
        $messageText = '';
        $this->injectMockNeverUsedTwilio();
        $expectedResponseCode = 422;

        // Act
        $this->actingAs($user)->json('post', '/driver/' . $driver['_id'].'/message',
            [
                'message_text' => $messageText
            ]);

        // Assert
        $this->assertResponseStatus($expectedResponseCode);
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
        $driver = $this->driverSet[0];
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
        $this->seeJsonStructure([
            '_id',
            'message_text',
            'queued_at',
            'status',
            'driver' => ['_id',
                'first_name',
                'last_name'
            ]]);
        $this->seeJson(
            [
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
            'status' => $expectedStatus
        ]);
        return $data;
    }


}
