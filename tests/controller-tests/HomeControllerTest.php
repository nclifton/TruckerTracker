<?php
require_once __DIR__ . '/../TruckerTrackerTestTrait.php';

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class HomeControllerTest extends TestCase {

    use TruckerTrackerTestTrait;

    protected function getFixture()
    {

        return [
            'users' => [],
            'password_resets' => [],
            'organisations' => $this->orgset,
            'drivers' => $this->driverset,
            'vehicles' => $this->vehicleset,
            'messages' => $this->messageset,
            'locations' => $this->locationset
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
     * A basic test example.
     *
     * @return void
     *
     * @test
     */
    public function homePagePanelsShown()
    {
        // Arrange
        $user = $this->firstUser();

        // Act
        $this->actingAs($user)->get('/home');

        // Assert
        $this->assertResponseOk();
        $this->seeElement('#heading_organisation_name');
        $this->seeInElement('#heading_organisation_name',$this->orgset[0]['name']);

        $this->seeElement('#message'.$this->messageset[0]['_id']);
        $this->seeElement('#location'.$this->locationset[0]['_id']);

    }
}
