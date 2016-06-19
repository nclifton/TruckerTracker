<?php

namespace TruckerTracker;

require_once __DIR__ . '/IntegratedTestCase.php';

class LoginPermissionsTest extends IntegratedTestCase
{


    protected function getFixture()
    {
        return [
            'users' => $this->fixtureUserset,
            'password_resets' => [],
            'drivers' => $this->driverset,
            'organisations' => $this->orgset,
            'vehicles' => $this->vehicleset,
            'messages' => $this->messageset,
            'locations' => $this->locationSet
        ];
    }

    /*
     * @before
     */
    public function setUp()
    {
        parent::setUp();

    }

    /**
     * The login as first user
     * - edit organisation button enabled.
     * - add driver and vehicle buttons enabled
     * - edit/delete driver and vehicle buttons enabled
     *
     * @test
     */
    public function firstUserLogin()
    {
        // Arrange
        // Act

        $this->login();


        // Assert
        $this->see('McSweeney Transport Group');
        $this->seeById("btn-edit-org","edit organisation enabled");
        $this->seeById('btn-add-driver','add driver enabled');
        $this->seeByCssSelector('button.open-modal-driver[value="'.$this->driverset[0]['_id'].'"]','edit driver 0 enabled');
        $this->seeByCssSelector('button.delete-driver[value="'.$this->driverset[0]['_id'].'"]','delete driver 0 enabled');
        $this->seeByCssSelector('button.open-modal-driver[value="'.$this->driverset[1]['_id'].'"]','edit driver 1 enabled');
        $this->seeByCssSelector('button.delete-driver[value="'.$this->driverset[1]['_id'].'"]','delete driver 1 enabled');
        $this->seeById('btn-add-vehicle','add vehicle enabled');
        $this->seeByCssSelector('button.open-modal-vehicle[value="'.$this->vehicleset[0]['_id'].'"]','edit vehicle 0 enabled');
        $this->seeByCssSelector('button.delete-vehicle[value="'.$this->vehicleset[0]['_id'].'"]','delete vehicle 0 enabled');
        $this->seeByCssSelector('button.open-modal-vehicle[value="'.$this->vehicleset[1]['_id'].'"]','edit vehicle 1 enabled');
        $this->seeByCssSelector('button.delete-vehicle[value="'.$this->vehicleset[1]['_id'].'"]','delete vehicle 1 enabled');

    }
    /**
     * The login as first user
     * - permission denied
     *
     * @test
     */
    public function twilioUserLogin()
    {
        // Arrange
        // Act

        $this->login(1);


        // Assert
        $this->see('Permission denied');

    }
    /**
     * The login as an operations user
     * - edit organisation button disabled.
     * - add driver and vehicle buttons disabled
     * - edit/delete driver and vehicle buttons disabled
     *
     * @test
     */
    public function operationsUserLogin()
    {
        // Arrange
        // Act

        $this->login(2);

        // Assert
        $this->see('McSweeney Transport Group');
        $this->notSeeId("btn-edit-organisation","edit organisation button disabled");
        $this->notSeeId('btn-add-driver','add driver button disabled');
        $this->notSeeCssSelector('button.open-modal-driver[value="'.$this->driverset[0]['_id'].'"]','edit driver 0 disabled');
        $this->notSeeCssSelector('button.delete-driver[value="'.$this->driverset[0]['_id'].'"]','delete driver 0 disabled');
        $this->notSeeCssSelector('button.open-modal-driver[value="'.$this->driverset[1]['_id'].'"]','edit driver 1 disabled');
        $this->notSeeCssSelector('button.delete-driver[value="'.$this->driverset[1]['_id'].'"]','delete driver 1 disabled');
        $this->notSeeId('btn-add-vehicle','add vehicle enabled');
        $this->notSeeCssSelector('button.open-modal-vehicle[value="'.$this->vehicleset[0]['_id'].'"]','edit vehicle 0 disabled');
        $this->notSeeCssSelector('button.delete-vehicle[value="'.$this->vehicleset[0]['_id'].'"]','delete vehicle 0 disabled');
        $this->notSeeCssSelector('button.open-modal-vehicle[value="'.$this->vehicleset[1]['_id'].'"]','edit vehicle 1 disabled');
        $this->notSeeCssSelector('button.delete-vehicle[value="'.$this->vehicleset[1]['_id'].'"]','delete vehicle 1 disabled');

    }

}
