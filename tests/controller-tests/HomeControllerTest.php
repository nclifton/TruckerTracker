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
            'organisations' => $this->orgset,
            'drivers' => $this->driverset,
            'vehicles' => $this->vehicleset,
            'messages' => $this->messageset,
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

        // Act
        $this->actingAs($user)->get('/home');

        // Assert
        $this->assertResponseOk();
        $this->seeElement('#heading_org_name');
        $this->seeInElement('#heading_org_name',$this->orgset[0]['name']);

        $this->seeElement('#message'.$this->messageset[0]['_id']);
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

        // Act
        $this->actingAs($user)->get('/home');

        // Assert
        $this->assertResponseOk();
        $this->seeElement('#heading_org_name');
        $this->seeInElement('#heading_org_name',$this->orgset[0]['name']);

        $this->seeElement('#message'.$this->messageset[0]['_id']);
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
        $this->actingAs($user)->get('/home');

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
        $this->actingAs($user)->get('/home');

        // Assert
        $this->assertResponseOk();
        $this->seeElement('#heading_org_name');

    }

}
