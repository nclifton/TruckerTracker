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

use Artisan;
use DB;
use PHPUnit_Framework_Exception;

include_once 'IntegratedTestCase.php';


class ConfigDriversTest extends IntegratedTestCase
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
        Artisan::call('db:seed', ['--class' => 'ConfigDriversTestDbSeeder']);
    }

    /**
     * Can Add a driver and can delete driver
     *
     * @return void
     *
     * @test
     */
    public function adds_edits_and_deletes_driver()
    {
        // Arrange
        $driver = $this->driverSet[0];
        $driver2 = $this->driverSet[1];


        // Act
        $this->login()->addDriver($driver);

        // Assert

        $this->seeInDatabase('drivers',$this->bind_driver($driver));

        $cursor = DB::collection('drivers')
            ->where(array_merge($this->bind_driver($driver),[
                'organisation_id' => ['$exists' => true]]
            ))->get();
        $id = null;
        foreach ($cursor as $doc){
            $id = $doc['_id'];
        }
        $this->assertNotNull($id);
        $this->assertThat($this->byId('driverModalLabel')->displayed(), $this->isFalse());
        $this->byCssSelector('#accordion a[href="#message_drivers_collapsible"]')->click();
        $this->wait();
        $this->assertThat($this->byId('driver'.$id)->displayed(), $this->isTrue());

        // check driver info displayed
        $this
            ->assertThat($this
                ->byCssSelector('#driver'.$id.' .name')
                ->text(),$this
                ->equalTo($driver['first_name'].' '.$driver['last_name']));

        // check added driver is clickable
        $this->assertThat($this->byId('btn-messageDriver')->attribute('disabled'),$this->equalTo('true'));
        $this->byId('driver' . $id )->click();
        $this->assertThat($this->byId('btn-messageDriver')->attribute('disabled'),$this->equalTo(null));
        $this->byId('btn-messageDriver')->click();
        $this->wait();
        $this->assertThat($this->byId('messageDriverModal')->displayed(), $this->isTrue());
        $this->byCssSelector('#messageDriverModal button.close')->click();
        $this->wait();
        $this->byId('btn-edit-driver')->click();
        $this->wait();
        $this->assertThat($this->byId('driverModal')->displayed(), $this->isTrue());

        $this->cleartype($driver2['first_name'], '#first_name');
        $this->cleartype($driver2['last_name'], '#last_name');
        $this->cleartype($driver2['mobile_phone_number'], '#driver_mobile_phone_number');
        $this->cleartype($driver2['drivers_licence_number'], '#drivers_licence_number');
        $this->clickOnElement('btn-save-driver');

        //$this->byCssSelector('#driverModal button.close')->click();
        $this->wait();
        $this->byId('btn-delete-driver')->click();
        $this->wait();
        $cursor = DB::collection('drivers')
            ->where($this->bind_driver($driver))->get();
        $this->assertCount(0,$cursor);
        $this->notSeeId('#driver' . $id, 'driver line not deleted');

    }

    /**
     * display validation message first name
     *
     * @test
     */
    public function blank_first_name_validation_fail()
    {
        // Arrange
        $driver = $this->driverSet[0];
        $driver['first_name'] = '';

        // Act
        $this->login()->addDriver($driver);

        // Assert
        $this->assertThat($this->byId('driverModalLabel')->text(), $this->equalTo('Driver Editor'));
        $this->assertThat(explode(' ', $this->byCssSelector('#driverForm div:first-child')->attribute('class')),
            $this->contains('has-error'));
        $this->assertThat($this->byCssSelector('#driverForm div:first-child span.help-block')->text(),
            $this->equalTo('We do need to have first names for your drivers'));

        // check that the validation error is reset away if we close the modal;
        $this->byCssSelector('#driverModal button.close')->click();
        $this->wait();
        $this->clickOnElement('btn-add-driver');
        $this->wait();
        $this->assertThat(explode(' ', $this->byCssSelector('#driverForm div:first-child')->attribute('class')),
            $this->logicalNot($this->contains('has-error')));
        $this->notSeeCssSelector('span.help-block');


    }



    /**
     * display validation message last name
     *
     * @test
     */
    public function blank_last_name_validation_fail()
    {
        // Arrange
        $driver = $this->driverSet[0];
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
    public function phone_number_validation_fail()
    {
        // Arrange
        $driver = $this->driverSet[0];
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
    public function another_phone_number_validation_fail()
    {
        // Arrange
        $driver = $this->driverSet[0];
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
    public function drivers_licence_validation_fail()
    {
        // Arrange
        $driver = $this->driverSet[0];
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
     *
     * @test
     */
    public function add_two_drivers()
    {
        // Arrange
        $driver1 = $this->driverSet[0];
        $driver2 = $this->driverSet[1];

        // Act
        $this->login()->addDriver($driver1)->addDriver($driver2);

        // Assert

        $cursor = DB::collection('drivers')
            ->where(array_merge($this->bind_driver($driver2), [
                    'organisation_id' => ['$exists' => true]])
            )->get();
        $this->assertCount(1, $cursor);

        $this->assertThat($this->byId('driverModalLabel')->displayed(), $this->isFalse());

        $driverSet = DB::collection('drivers')
            ->where(['organisation_id' => $driver1['organisation_id']])->get();
        $ids = [];
        foreach ($driverSet as $driver){
            $ids[] = $driver['_id'];
        }

        // select first driver then select second driver, first driver should still be selected
        $this->byCssSelector('a[href="#message_drivers_collapsible"]')->click();
        $this->wait();
        $this->byId('driver'.$ids[0])->click();
        $this->wait();
        $this->byIdHasClass('driver'.$ids[0],'selected');
        $this->byId('driver'.$ids[1])->click();
        $this->wait();
        $this->byIdHasClass('driver'.$ids[1],'selected');
        $this->byIdHasClass('driver'.$ids[0],'selected');

        // select the selected driver and should de-select
        $this->byId('driver'.$ids[1])->click();
        $this->byIdNotHasClass('driver'.$ids[1],'selected');
        $this->byIdHasClass('driver'.$ids[0],'selected');




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

    private function byIdHasClass($id, $array)
    {
        $classAttribute = $this->byId($id)->attribute('class');
        $classes = explode(' ',$classAttribute);
        if (!in_array($array,$classes)) {
            throw new PHPUnit_Framework_Exception('Element does not have specified class/es');
        }
    }

    private function byIdNotHasClass($id, $array)
    {
        $classAttribute = $this->byId($id)->attribute('class');
        $classes = explode(' ',$classAttribute);
        if (in_array($array,$classes)) {
            throw new PHPUnit_Framework_Exception('Element does have specified class/es');
        }
    }


}