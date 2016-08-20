<?php

namespace TruckerTracker;

use Artisan;

require_once __DIR__ . '/IntegratedTestCase.php';

class LoginPermissionsTest extends IntegratedTestCase
{

    protected function artisanSeedDb()
    {
        Artisan::call('db:seed', ['--class' => 'LoginPermissionsTestDbSeeder']);
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
        $this->seeById("btn-edit-org","edit organisation seen");
        $this->seeById('btn-add-driver','add driver seen');
        $this->seeById('btn-add-vehicle','add vehicle seen');
        $this->byCssSelector('a[href="#message_drivers_collapsible"]')->click();
        $this->wait();
        $this->seeById('btn-edit-driver','edit driver seen');
        $this->seeById('btn-delete-driver','delete driver seen');
        $this->seeById('btn-messageDriver','message driver seen');
        $this->seeById('btn-delete-messages','delete message seen');
        $this->byCssSelector('a[href="#locate_vehicles_collapsible"]')->click();
        $this->wait();
        $this->seeById('btn-locateVehicle','locate vehicle seen');
        $this->seeById('btn-delete-vehicle','delete vehicle seen');
        $this->seeById('btn-edit-vehicle','edit vehicle seen');
        $this->seeById('btn-view-locations','view vehicle locations seen');
        $this->seeById('btn-delete-locations','delete vehicle locations seen');

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
        $this->notSeeCssSelector('button.open-modal-driver[value="'.$this->driverSet[0]['_id'].'"]','edit driver 0 disabled');
        $this->notSeeCssSelector('button.delete-driver[value="'.$this->driverSet[0]['_id'].'"]','delete driver 0 disabled');
        $this->notSeeCssSelector('button.open-modal-driver[value="'.$this->driverSet[1]['_id'].'"]','edit driver 1 disabled');
        $this->notSeeCssSelector('button.delete-driver[value="'.$this->driverSet[1]['_id'].'"]','delete driver 1 disabled');
        $this->notSeeId('btn-add-vehicle','add vehicle enabled');
        $this->notSeeCssSelector('button.open-modal-vehicle[value="'.$this->vehicleSet[0]['_id'].'"]','edit vehicle 0 disabled');
        $this->notSeeCssSelector('button.delete-vehicle[value="'.$this->vehicleSet[0]['_id'].'"]','delete vehicle 0 disabled');
        $this->notSeeCssSelector('button.open-modal-vehicle[value="'.$this->vehicleSet[1]['_id'].'"]','edit vehicle 1 disabled');
        $this->notSeeCssSelector('button.delete-vehicle[value="'.$this->vehicleSet[1]['_id'].'"]','delete vehicle 1 disabled');

    }

}
