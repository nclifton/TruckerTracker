<?php
namespace TruckerTracker;
require_once __DIR__ . '/TwilioControllerTestCase.php';

use Artisan;
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

    protected function artisanSeedDb()
    {
        Artisan::call('db:seed', ['--class' => 'TwilioControllerLocationTestDbSeeder']);
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
        $this->actingAs($user)->json('post','/vehicle/'.$this->vehicleSet[0]['_id'].'/location', []);

        // Assert
        $this->assertResponseStatus(403);

    }

    /**
     * @param $user
     */
    protected function assertLocationRequestSentToTwilio($user)
    {
        $vehicle = $this->vehicleSet[0];
        $expectedMessageText = "WHERE,${vehicle['tracker_password']}#";
        $expectedStatus = 'queued';
        $twilioUser = $this->twilioUser();
        $org = $this->orgSet[0];
        $this->mockTwilio($org, $vehicle['mobile_phone_number'], $twilioUser->username, $expectedMessageText, $expectedStatus);

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
        $this->seeJson(
            [
                'registration_number' => $vehicle['registration_number']
            ]);
        $this->seeInDatabase('locations', [
            '_id' => $data['_id'],
            'vehicle_id' => $vehicle['_id'],
            'organisation_id' => $org['_id'],
            'status' => $expectedStatus
        ]);
    }
}
