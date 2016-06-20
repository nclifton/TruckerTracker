<?php
namespace TruckerTracker;
require_once __DIR__ . '/TwilioControllerTestCase.php';

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use TruckerTracker\Twilio\TwilioInterface;
use \Mockery as m;

class TwilioControllerLocationTest extends TwilioControllerTestCase
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

        //$this->orgset[0] = array_merge($this->orgset[0],$this->twilio_cwf);

        return [
            'users' => [],
            'password_resets' => [],
            'organisations' => $this->orgset,
            'drivers' => $this->driverset,
            'vehicles' => $this->vehicleset,
            'messages' => [],
            'locations' => $this->locationSet
        ];
    }
    /**
     * Post location request message.
     *
     * @return void
     *
     * @test
     */
    public function locationRequestFirstUserSuccess()
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
    public function locationRequestByOpsUserSuccess()
    {

        // Arrange
        $user = $this->user();
        
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
    public function locationRequestByTwilioUserFailsUnauthorised()
    {

        // Arrange
        $user = $this->twilioUser();

        // Act
        $this->actingAs($user)->json('post','/vehicle/'.$this->vehicleset[0]['_id'].'/location', []);

        // Assert
        $this->assertResponseStatus(403);

    }

    /**
     * @param $user
     */
    protected function assertLocationRequestSentToTwilio($user)
    {
        $vehicle = $this->vehicleset[0];
        $expectedMessageText = "WHERE,${vehicle['tracker_password']}#";
        $expectedStatus = 'queued';
        $twilioUser = $this->twilioUser();
        $org = $this->orgset[0];
        $this->injectMockTwilio($org, $vehicle['mobile_phone_number'], $twilioUser->username, $expectedMessageText, $expectedStatus);

        // Act
        $this->actingAs($user)->json('post', '/vehicle/' . $vehicle['_id'].'/location', []);

        // Assert
        try{
            m::close();
        }catch(\Exception $e){
            $str = $this->subset . '';
            \Log::debug($str);
        }

        $data = json_decode($this->response->getContent(), true);
        $this->assertResponseOk();
        $this->seeJsonStructure(['_id', 'queued_at', 'status', 'vehicle' => ['registration_number']]);
        $this->seeJson(
            [
                'status' => $expectedStatus
            ]);
        $this->seeInDatabase('locations', [
            '_id' => $data['_id'],
            'vehicle_id' => $vehicle['_id'],
            'organisation_id' => $org['_id'],
            'status' => $expectedStatus
        ]);
    }
}
