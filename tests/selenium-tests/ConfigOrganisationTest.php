<?php

include_once 'SeleniumTestLoader.php';

use PHPUnit_Extensions_Selenium2TestCase_Keys as Keys;
use \Zumba\PHPUnit\Extensions\Mongo\DataSet\DataSet;

class ConfigOrganisationTest extends SeleniumTestLoader
{


    protected $orgset = [
        'test'=>[
            'name' => 'McSweeney Transport Group'
        ],
        'blank'=>[
            'name' => ''
        ],
        'long'=>[
            'name' => 'a very long organisation name has to be more than 128 character long like this one when I add a lot fo words to the organisation name'
         ],
        'other'=>[
            'name' => 'Some Other Organisation'
         ]

    ];


    protected function getFixture()
    {
        $userset = $this->fixtureUserset;
        foreach ($userset as $key => $user){
            unset($userset[$key]['organisation_id']);
        }
        return [
            'users' => $userset,
            'password_resets' => [],
            'drivers' => [],
            'organisations' => [],
            'vehicles' => []
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
     * close and reopen the organisation dialog
     *
     * @return void
     */
    public function testReopenOrgDialog()
    {

        // Act
        $this->login()->closeOrganisationDialog();
        $this->byId('btn-add-organisation')->click();
        sleep(1);


        // Assert
        $this->assertThat($this->byId('organisationModalLabel')->text(), $this->equalTo('Organisation Editor'));
        $this->assertThat($this->byId('btn-save-organisation')->text(), $this->equalTo('Add Organisation'));

    }

    /**
     * can add organisation
     *
     * @return void
     */
    public function testAddsOrganisation()
    {
        // Arrange

        // Act
        $this->login()->addOrganisation();

        // Assert
        $this->assertThat($this->byId('heading_organisation_name')->text(), 
            $this->equalTo($this->orgset['test']['name']));

        $this->assertThat($this->byId('btn-add-organisation')->displayed(), $this->isFalse());
        $this->assertThat($this->byId('btn-edit-organisation')->displayed(), $this->isTrue());

        $this->assertThat($this->byId('btn-add-driver')->attribute('disabled'), $this->isNull());
        $this->assertThat($this->byId('btn-add-vehicle')->attribute('disabled'), $this->isNull());

        $this->assertCount(1, $this->getMongoConnection()
            ->collection('users')
            ->find(['name' => 'test user 1','organisation_id'=>['$exists'=>true]]));

     }

    /**
     * can add organisation
     *
     * @return void
     */
    public function testAddEmptyOrganisationValidationFail()
    {
        // Arrange

        // Act
        $this->login()->addOrganisation('blank');

        // Assert
        $this->assertThat($this->byId('organisationModalLabel')->text(), $this->equalTo('Organisation Editor'));
        $this->assertThat(explode(' ', $this->byCssSelector('#frmOrganisation div:first-child')->attribute('class')),
            $this->contains('has-error'));
        $this->assertThat($this->byCssSelector('#frmOrganisation div:first-child span.help-block')->text(),
            $this->equalTo('We do need a name for your organisation'));


    }

    /**
     * duplicate organisation allowed
     *
     * @return void
     */
    public function testAddDuplicateOrganisationValidationPass()
    {
        // Arrange
        $org = $this->orgset['test'];
        $this->dbAddOrganisation('test');

        // Act
        $this->login()->addOrganisation('test');

        // Assert
        $this->assertCount(2, $this->getMongoConnection()
            ->collection('organisations')
            ->find(['name' => $org['name']]),$org['name'].' is in organisation');
        $this->assertCount(1, $this->getMongoConnection()
            ->collection('users')
            ->find(['name' => 'test user 1','organisation_id'=>['$exists'=>true]]), 'user count');


    }


    /**
     * cannot add long organisation
     *
     * @return void
     */
    public function testAddLongOrganisationValidationFail()
    {

        // Act
        $this->login()->addOrganisation('long');
        sleep(2);

        // Assert
        $this->assertThat($this->byId('organisationModalLabel')->text(), $this->equalTo('Organisation Editor'));
        $this->assertThat(explode(' ', $this->byCssSelector('#frmOrganisation div:first-child')->attribute('class')),
            $this->contains('has-error'));
        $this->assertThat($this->byCssSelector('#frmOrganisation div:first-child span.help-block')->text(),
            $this->equalTo('That name for you organisation is too long, make it less than 128'));

    }

     /**
     * edit added organisation dialog
     *
     * @return void
     */
    public function testEditOrganisation()
    {

        // Act
        $this->login()->addOrganisation()->byId('btn-edit-organisation')->click();
        sleep(2);

        // Assert
        $this->assertThat($this->byId('organisationModalLabel')->text(), $this->equalTo('Organisation Editor'));
        $this->assertThat($this->byId('btn-save-organisation')->text(), $this->equalTo('Save Changes'));

        // Act some more
        $this->addOrganisation('other');

        // Assert some more
        $this->assertThat($this->byId('heading_organisation_name')->text(),
            $this->equalTo($this->orgset['other']['name']));

        $this->assertCount(1, $this->getMongoConnection()
            ->collection('organisations')
            ->find(['name' => $this->orgset['other']['name']] ));

    }



    /**
     * Diver Dialog displayed to add vehicle
     *
     * @return void
     */
    public function testAddVehicleDialogDisplayed()
    {
        // Arrange

        // Act
        $this->login()->addOrganisation();
        $this->clickOnElement('btn-add-vehicle');
        sleep(2); // wait for animation

        // Assert
        $this->assertThat($this->byId('vehicleModalLabel')->displayed(), $this->isTrue());

    }

    /**
     * Can Add a Vehicle
     *
     * @return void
     */
    public function testAddsVehicle()
    {
        // Arrange

        // Act
        $this->login()->addOrganisation();
        $this->addVehicle();

        // Assert
        $vehicle = $this->vehicleset[0];
        $this->assertCount(1, $this->getMongoConnection()
            ->collection('vehicles')
            ->find([
                'registration_number' => $vehicle['registration_number'],
                'mobile_phone_number' => $vehicle['mobile_phone_number'],
                'tracker_imei_number' => $vehicle['tracker_imei_number'],
                'organisation_id' => ['$exists'=>true]
            ] ));

        $this->assertThat($this->byId('vehicleModalLabel')->displayed(), $this->isFalse());

    }


    protected function addOrganisation($orgkey = 'test')
    {
        $organisation = $this->orgset[$orgkey];
        $this->clearType($organisation['name'], '#organisation_name');
        $this->byId('btn-save-organisation')->click();
        sleep(3); // wait for animation
        return $this;
    }

    protected function addVehicle($vKey = 0)
    {
        $vehicle = $this->vehicleset[$vKey];

        $this->clickOnElement('btn-add-vehicle');
        sleep(2); // wait for animation

        $this->clearType($vehicle['registration_number'], '#registration_number');
        $this->clearType($vehicle['mobile_phone_number'], '#vehicle_mobile_phone_number');
        $this->clearType($vehicle['tracker_imei_number'], '#tracker_imei_number');
        $this->clickOnElement('btn-save-vehicle');
        sleep(2); // wait for animation

        return $this;
    }

    private function dbAddOrganisation($orgkey = 'test')
    {

        $this->getMongoConnection()->collection('organisations')->insert($this->orgset[$orgkey]);
        return $this;
    }

    protected function closeOrganisationDialog()
    {
        $this->byCssSelector('#organisationModal > .modal-dialog > .modal-content > .modal-header > button.close')->click();
        sleep(2);
        return $this;
    }


}
