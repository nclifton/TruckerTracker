<?php
namespace TruckerTracker;
require_once __DIR__ . '/MessageControllerTestCase.php';

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use TruckerTracker\Twilio\TwilioInterface;

class MessageControllerDeleteMessageTest extends MessageControllerTestCase
{

    /**
     * @before
     */
    public function setUp()
    {
        parent::setUp();
    }



    /**
     * get location data
     *
     * @test
     */
    public function deleteMessage()
    {
        // Arrange
        $org = $this->orgSet[0];
        $user = $this->firstUser();
        $msg = $this->messageSet[0];
        $expectedMsg = $msg;
        unset($expectedMsg['driver_id']);
        unset($expectedMsg['organisation_id']);
        unset($expectedMsg['sid']);


        // Act
        $this->actingAs($user)->json('delete','driver/message/'. $msg['_id']);

        // Assert
        $this->assertResponseOk();

        $this->seeJson($expectedMsg);

        $this->notSeeInDatabase('messages',['_id'=>$msg['_id']]);

    }


 
}
