<?php
/**
 *
 * @version 0.0.1: TestConfigVehicles.php 4/06/2016T00:49
 * @author Clifton IT Foundries Pty Ltd
 * @link http://cliftonwebfoundry.com.au
 * @copyright Copyright (c) 2016 Clifton IT Foundries Pty Ltd. All rights Reserved
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 *
 **/
include_once 'SeleniumTestLoader.php';

use Zumba\PHPUnit\Extensions\Mongo\DataSet\DataSet;

class ConfigVehiclesSeleniumTest extends SeleniumTestLoader
{

    /**
     * @var array
     */
    protected function getFixture()
    {
        return [
            'users' => $this->fixtureUserset,
            'password_resets' => [],
            'organisations' => $this->orgset,
            'drivers' => [],
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
     * Diver Dialog displayed to add driver
     *
     * @return void
     */
    public function testAddDriverDialogDisplayed()
    {
        // Arrange


        // Act
        $this->login()->clickOnElement('btn-add-vehicle');
        sleep(2); // wait for animation

        // Assert
        $this->assertThat($this->byId('vehicleModalLabel')->displayed(), $this->isTrue());

    }


    /**
     * Can Add a vehicle and delete a vehicle
     *
     * @return void
     */
    public function testAddsVehicle()
    {
        // Arrange
        $v = $this->vehicleset[0];

        // Act
        $this->login()->addVehicle($v);

        // Assert

        $cursor = $this->getMongoConnection()
            ->collection('vehicles')
            ->find($this->bind_vehicle($v));
        $id = null;
        $cnt = 0;
        foreach ($cursor as $doc){
            $id = $doc['_id'];
            $cnt++;
        }
        $this->assertEquals(1, $cnt);
        $this->assertNotNull($id);

        $this->assertThat($this->byId('vehicleModalLabel')->displayed(), $this->isFalse());
        $this->assertThat($this->byId('vehicle'.$id)->displayed(), $this->isTrue());

        // check added vehicle line buttons

        $this->byCssSelector('#vehicle' . $id .' .open-modal-locate')->click();
        sleep(3);
        $this->assertThat($this->byId('locateModal')->displayed(), $this->isTrue());
        $this->byCssSelector('#locateModal button.close')->click();
        sleep(3);
        $this->byCssSelector('#vehicle'.$id.' .open-modal-vehicle')->click();
        sleep(3);
        $this->assertThat($this->byId('vehicleModal')->displayed(), $this->isTrue());
        $this->byCssSelector('#vehicleModal button.close')->click();
        sleep(1);
        $this->byCssSelector('#vehicle'.$id.' .delete-vehicle')->click();
        sleep(3);
        $cursor = $this->getMongoConnection()
            ->collection('vehicles')
            ->find($this->bind_vehicle($v));
        $this->assertCount(0,$cursor);
        $this->notSeeElement(function() use ($id){
            $this->byId('#vehicle'.$id);
        },'vehicle line not deleted');


    }

    /**
     * display validation message registration number
     *
     * @test
     */
    public function testBlankRegoValidationFail()
    {
        // Arrange
        $vehicle = $this->vehicleset[0];
        $vehicle['registration_number'] = '';

        // Act
        $this->login()->addVehicle($vehicle);

        // Assert
        $this->assertThat($this->byId('vehicleModalLabel')->text(), $this->equalTo('Vehicle Editor'));
        $this->assertThat(explode(' ', $this->byCssSelector('#frmVehicle div:first-child')->attribute('class')),
            $this->contains('has-error'));
        $this->assertThat($this->byCssSelector('#frmVehicle div:first-child span.help-block')->text(),
            $this->equalTo('We know the vehicles by their registration numbers'));

    }
    /**
     * display validation message registration number
     *
     * @test
     */
    public function testLowercaseRegoToUppercase()
    {
        // Arrange
        $v = $this->vehicleset[0];
        $v['registration_number'] = 'aac993';

        // Act
        $this->login()->addVehicle($v);

        $v['registration_number'] = 'AAC993';
        // Assert
        $this->assertCount(1, $this->getMongoConnection()
            ->collection('vehicles')
            ->find($this->bind_vehicle($v)));

        $this->assertThat($this->byId('vehicleModalLabel')->displayed(), $this->isFalse());

    }
    /**
     * validation phone number is displayed
     *
     * @test
     */
    public function testPhoneNumberValidationFail()
    {
        // Arrange
        $vehicle = $this->vehicleset[0];
        $vehicle['mobile_phone_number'] = '0419X40683';

        // Act
        $this->login()->addVehicle($vehicle);

        // Assert
        $this->assertThat($this->byId('vehicleModalLabel')->text(), $this->equalTo('Vehicle Editor'));
        $attribute = $this->byXPath('//form[@id="frmVehicle"]/div[2]')->attribute('class');
        $this->assertThat(explode(' ', $attribute),
            $this->contains('has-error'));
        $this->assertThat($this->byXPath('//form[@id="frmVehicle"]/div[2]/div/span/strong')->text(),
            $this->equalTo('That doesn\'t look like a phone number, it needs to have 10 digits and start with a 0 or start with +61 and have 11 digits'));

    }
    /**
     * validation phone number is displayed
     *
     * @test
     */
    public function testImeiValidationFail()
    {
        // Arrange
        $vehicle = $this->vehicleset[0];
        $vehicle['tracker_imei_number'] = '12345678901234';

        // Act
        $this->login()->addVehicle($vehicle);

        // Assert
        $this->assertThat($this->byId('vehicleModalLabel')->text(), $this->equalTo('Vehicle Editor'));
        $attribute = $this->byXPath('//form[@id="frmVehicle"]/div[3]')->attribute('class');
        $this->assertThat(explode(' ', $attribute),
            $this->contains('has-error'));
        $this->assertThat($this->byXPath('//form[@id="frmVehicle"]/div[3]/div/span/strong')->text(),
            $this->equalTo('That\'s not an IMEI number, they are a string of digits between 15 and 17 digits long'));

    }

    /**
     * @param $v
     * @return array
     */
    protected function bind_vehicle($v)
    {
        return [
            'registration_number' => strtoupper($v['registration_number']),
            'mobile_phone_number' => $v['mobile_phone_number'],
            'tracker_imei_number' => $v['tracker_imei_number'],
            'organisation_id' => ['$exists' => true]
        ];
    }
    protected function addVehicle($vehicle)
    {
        $this->clickOnElement('btn-add-vehicle');
        sleep(2); // wait for animation

        $this->clearType($vehicle['registration_number'], '#registration_number');
        $this->clearType($vehicle['mobile_phone_number'], '#vehicle_mobile_phone_number');
        $this->clearType($vehicle['tracker_imei_number'], '#tracker_imei_number');
        $this->clickOnElement('btn-save-vehicle');
        sleep(2); // wait for animation
        return $this;
    }

}