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

namespace TruckerTracker;

include_once 'IntegratedTestCase.php';


class ConfigDriversTest extends IntegratedTestCase
{

    
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
     * Can Add a driver and can delete driver
     *
     * @return void
     */
    public function testAddsDriver()
    {
        // Arrange
        $driver = $this->driverset[0];

        // Act
        $this->login()->addDriver($driver);

        // Assert

        $this->seeInDatabase('drivers',$this->bind_driver($driver));

        $cursor = $this->getMongoConnection()
            ->collection('drivers')
            ->find(array_merge($this->bind_driver($driver),[
                'organisation_id' => ['$exists' => true]]
            ));
        $id = null;
        foreach ($cursor as $doc){
            $id = $doc['_id'];
        }
        $this->assertNotNull($id);
        $this->assertThat($this->byId('driverModalLabel')->displayed(), $this->isFalse());
        $this->assertThat($this->byId('driver'.$id)->displayed(), $this->isTrue());

        // check added driver line buttons

        $this->byCssSelector('#driver' . $id .' .open-modal-message')->click();
        sleep(3);
        $this->assertThat($this->byId('messageModal')->displayed(), $this->isTrue());
        $this->byCssSelector('#messageModal button.close')->click();
        sleep(3);
        $this->byCssSelector('#driver'.$id.' .open-modal-driver')->click();
        sleep(3);
        $this->assertThat($this->byId('driverModal')->displayed(), $this->isTrue());
        $this->byCssSelector('#driverModal button.close')->click();
        sleep(1);
        $this->byCssSelector('#driver'.$id.' .delete-driver')->click();
        sleep(3);
        $cursor = $this->getMongoConnection()
            ->collection('drivers')
            ->find($this->bind_driver($driver));
        $this->assertCount(0,$cursor);
        $this->notSeeId('#driver' . $id, 'driver line not deleted');

    }

    /**
     * display validation message first name
     *
     * @test
     */
    public function testBlankFirstNameValidationFail()
    {
        // Arrange
        $driver = $this->driverset[0];
        $driver['first_name'] = '';

        // Act
        $this->login()->addDriver($driver);

        // Assert
        $this->assertThat($this->byId('driverModalLabel')->text(), $this->equalTo('Driver Editor'));
        $this->assertThat(explode(' ', $this->byCssSelector('#driverForm div:first-child')->attribute('class')),
            $this->contains('has-error'));
        $this->assertThat($this->byCssSelector('#driverForm div:first-child span.help-block')->text(),
            $this->equalTo('We do need to have first names for your drivers'));

    }



    /**
     * display validation message last name
     *
     * @test
     */
    public function testBlankLastNameValidationFail()
    {
        // Arrange
        $driver = $this->driverset[0];
        $driver['last_name'] = '';

        // Act
        $this->login()->addDriver($driver);

        // Assert form#driverForm.form-horizontal div.form-group.error.has-error
        // //form[@id="driverForm"]/div[2]
        $this->assertThat($this->byId('driverModalLabel')->text(), $this->equalTo('Driver Editor'));
        $attribute = $this->byXPath('//form[@id="driverForm"]/div[2]')->attribute('class');
        $this->assertThat(explode(' ', $attribute),
            $this->contains('has-error'));
        // #driverForm > div.form-group.error.has-error
        $this->assertThat($this->byXPath('//form[@id="driverForm"]/div[2]/div/span/strong')->text(),
            $this->equalTo('We do need to have last names for your drivers'));

    }

    /**
     * validation phone number is displayed
     *
     * @test
     */
    public function testPhoneNumberValidationFail()
    {
        // Arrange
        $driver = $this->driverset[0];
        $driver['mobile_phone_number'] = '0419X40683';

        // Act
        $this->login()->addDriver($driver);

        // Assert
        $this->assertThat($this->byId('driverModalLabel')->text(), $this->equalTo('Driver Editor'));
        $attribute = $this->byXPath('//form[@id="driverForm"]/div[3]')->attribute('class');
        $this->assertThat(explode(' ', $attribute),
            $this->contains('has-error'));
        $this->assertThat($this->byXPath('//form[@id="driverForm"]/div[3]/div/span/strong')->text(),
            $this->equalTo('That doesn\'t look like an australian phone number, it needs to have 10 digits and start with a 0 or start with +61 and have 11 digits'));

    }

    /**
     * validation phone number is displayed
     *
     * @test
     */
    public function anotherPhoneNumberValidationFail()
    {
        // Arrange
        $driver = $this->driverset[0];
        $driver['mobile_phone_number'] = '298204732';

        // Act
        $this->login()->addDriver($driver);

        // Assert
        $this->assertThat($this->byId('driverModalLabel')->text(), $this->equalTo('Driver Editor'));
        $attribute = $this->byXPath('//form[@id="driverForm"]/div[3]')->attribute('class');
        $this->assertThat(explode(' ', $attribute),
            $this->contains('has-error'));
        $this->assertThat($this->byXPath('//form[@id="driverForm"]/div[3]/div/span/strong')->text(),
            $this->equalTo('That doesn\'t look like an australian phone number, it needs to have 10 digits and start with a 0 or start with +61 and have 11 digits'));

    }

    /**
     * that drivers licence number format is valid
     *
     * @test
     */
    public function testDriversLicenceValidationFail()
    {
        // Arrange
        $driver = $this->driverset[0];
        $driver['drivers_licence_number'] = 'XXYY00000';

        // Act
        $this->login()->addDriver($driver);

        // Assert
        $this->assertThat($this->byId('driverModalLabel')->text(), $this->equalTo('Driver Editor'));
        $attribute = $this->byXPath('//form[@id="driverForm"]/div[4]')->attribute('class');
        $this->assertThat(explode(' ', $attribute),
            $this->contains('has-error'));
        $this->assertThat($this->byXPath('//form[@id="driverForm"]/div[4]/div/span/strong')->text(),
            $this->equalTo('That drivers licence number looks odd, please check, should be 4 to 9 alpha numeric'));

    }
    /**
     * Can Add a driver
     *
     * @return void
     */
    public function testAddTwoDrivers()
    {
        // Arrange
        $driver1 = $this->driverset[0];
        $driver2 = $this->driverset[1];

        // Act
        $this->login()->addDriver($driver1)->addDriver($driver2);

        // Assert

        $this->assertCount(1, $this->getMongoConnection()
            ->collection('drivers')
            ->find(array_merge($this->bind_driver($driver2),[
                'organisation_id' => ['$exists' => true]])
            ));

        $this->assertThat($this->byId('driverModalLabel')->displayed(), $this->isFalse());

    }

    /**
     * @param $driver2
     * @return array
     */
    protected function bind_driver($driver2)
    {
        return [
            'first_name' => $driver2['first_name'],
            'last_name' => $driver2['last_name'],
            'mobile_phone_number' => $driver2['mobile_phone_number'],
            'drivers_licence_number' => strtoupper($driver2['drivers_licence_number'])];
    }


}