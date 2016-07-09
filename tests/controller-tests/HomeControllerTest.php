<?php

namespace TruckerTracker;

require_once __DIR__ . '/../TestTrait.php';
require_once __DIR__ . '/../TestCase.php';

class HomeControllerTest extends TestCase {

    use TestTrait;

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
     * @before
     */
    public function setUp()
    {
        parent::setUp();
    }

    /**
     * First user login
     *
     * @return void
     *
     * @test
     */
    public function homePagePanelsShownToFirstUser()
    {
        // Arrange
        $user = $this->firstUser();
        $tuser = $this->twilioUser();

        // Act
        $this->actingAs($user)->get('/dash');

        // Assert
        $this->assertResponseOk();
        $this->seeElement('#heading_org_name');
        $this->seeInElement('#heading_org_name',$this->orgSet[0]['name']);

        $this->seeElement('#message'.$this->messageSet[0]['_id']);
        $this->seeElement('#location'.$this->locationSet[0]['_id']);

    }

    /**
     * Ops user login
     *
     * @return void
     *
     * @test
     */
    public function homePagePanelsShownToOpsUser()
    {
        // Arrange
        $user = $this->user();
        $this->twilioUser();

        // Act
        $this->actingAs($user)->get('/dash');

        // Assert
        $this->assertResponseOk();
        $this->seeElement('#heading_org_name');
        $this->seeInElement('#heading_org_name',$this->orgSet[0]['name']);

        $this->seeElement('#message'.$this->messageSet[0]['_id']);
        $this->seeElement('#location'.$this->locationSet[0]['_id']);

    }
    /**
     * Twilio user login fails
     *
     * @return void
     *
     * @test
     */
    public function homePagePanelsShownToTwilioUser()
    {
        // Arrange
        $user = $this->twilioUser();

        // Act
        $this->actingAs($user)->get('/dash');

        // Assert
        $this->assertResponseStatus(403);

    }

    /**
     * initial newly registered user login
     *
     * @return void
     *
     * @test
     */
    public function homePagePanelsShownToNewUser()
    {
        // Arrange
        $user = factory(User::class)->create();

        // Act
        $this->actingAs($user)->get('/dash');

        // Assert
        $this->assertResponseOk();
        $this->seeElement('#heading_org_name');

    }

}
