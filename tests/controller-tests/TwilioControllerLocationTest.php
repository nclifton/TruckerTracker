<?php
require_once __DIR__ . '/TwilioControllerTestCase.php';

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use TruckerTracker\Twilio\TwilioInterface;

class TwilioControllerLocationTest extends TwilioControllerTestCase
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
    public function postLocationFirstUserRouteSuccess()
    {
        
        // Arrange
        $user = $this->firstUser();

        // Act
        // Assert
        $this->assertLocationRequestSentToTwilio($user);
        
    }

    /**
     * Post message.
     *
     * @return void
     *
     * @test
     */
    public function postLocationOpsUserRouteSuccess()
    {

        // Arrange
        $user = $this->createUser();
        
        // Act
        // Assert
        $this->assertLocationRequestSentToTwilio($user);

    }
    /**
     * Post message.
     *
     * @return void
     *
     * @test
     */
    public function postLocationTwilioUserFailsUnauthorised()
    {

        // Arrange
        $user = $this->twilioUser();

        // Act
        $this->actingAs($user)->json('post','/text/vehicle/'.$this->vehicleset[0]['_id'], []);

        // Assert
        $this->assertResponseStatus(403);


    }


    /**
     * @param $user
     */
    protected function assertLocationRequestSentToTwilio($user)
    {
        $org = $this->orgset[0];
        $vehicle = $this->vehicleset[0];
        $expectedMessageText = "WHERE,${vehicle['tracker_password']}#";
        $expectedStatus = 'queued';
        $twilioUser = $this->twilioUser();
        $this->injectMockTwilio($org, $vehicle['mobile_phone_number'], $twilioUser->email, $expectedMessageText, $expectedStatus);

        // Act
        $this->actingAs($user)->json('post', '/text/vehicle/' . $vehicle['_id'], []);

        // Assert
        $this->assertResponseOk();
        $this->seeJsonStructure(['_id', 'vehicle_id', 'organisation_id', 'queued_at', 'status', 'vehicle' => ['registration_number']]);
        $this->seeJson(
            [
                'vehicle_id' => $vehicle['_id'],
                'organisation_id' => $org['_id'],
                'status' => $expectedStatus
            ]);
        $data = json_decode($this->response->getContent(), true);
        $this->seeInDatabase('locations', [
            '_id' => $data['_id'],
            'vehicle_id' => $vehicle['_id'],
            'organisation_id' => $org['_id'],
            'status' => $expectedStatus
        ]);
    }
}
