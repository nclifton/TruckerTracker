<?php

namespace TruckerTracker;

require_once __DIR__ . '/ConfigControllerTestCase.php';

/**
 *
 * @version 0.0.1: ConfigControllerDriversTest.php 4/06/2016T06:25
 * @author Clifton IT Foundries Pty Ltd
 * @link http://cliftonwebfoundry.com.au
 * @copyright Copyright (c) 2016 Clifton IT Foundries Pty Ltd. All rights Reserved
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 *
 **/

class ConfigControllerVehiclesTest extends ConfigControllerTestCase
{
    
    /**
     * @before
     */
    public function setUp()
    {
        parent::setUp();
    }

    protected function getFixture()
    {
        return [
            'users' => [],
            'password_resets' => [],
            'organisations' => $this->orgset,
            'drivers' => [],
            'vehicles' => []
        ];
    }

    /**
     * test post vehicle as first user
     * - back-end adds the tracker password
     *
     * @test
     */
    public function addVehicleAddsVehicle()
    {
        // Arrange
        $user = $this->firstUser();
        $v = $this->vehicleset[0];
        $submittedVehicle = $this->bind_vehicle($v);
        unset($submittedVehicle['tracker_password']);

        // Act
        $this->actingAs($user)->json('post', '/vehicles', $submittedVehicle);

        // Assert
        $this->assertResponseOk();
        $this->seeJson($submittedVehicle);
        $this->seeJsonStructure(['_id','registration_number','mobile_phone_number','tracker_imei_number']);
        $this->seeInDatabase('vehicles', $this->bind_vehicle_Org_id($v));

        return $user;
    }
    /**
     * test post driver as twillio user - fails
     *
     * @test
     */
    public function asTwilioUserAddFailsUnauthorised()
    {
        // Arrange
        $user = $this->twilioUser();

        // Act

        $v = $this->vehicleset[0];
        $this->actingAs($user)->json('post', '/vehicles', $this->bind_vehicle($v));

        // Assert
        $this->assertResponseStatus(403);
    }
    /**
     * test post driver as operations user
     *
     * @test
     */
    public function asOpsUserAddVehicleFailsUnauthorised()
    {
        // Arrange
        $user = $this->user();

        // Act

        $v = $this->vehicleset[0];
        $this->actingAs($user)->json('post', '/vehicles', $this->bind_vehicle($v));

        // Assert
        $this->assertResponseStatus(403);
    }

    /**
     * test put driver as first user updates
     *
     * @test
     */
    public function putDriverFirstUserUpdates()
    {
        // Arrange
        $v2 = $this->vehicleset[1];
        $submitedVehicle = $this->bind_vehicle($v2);
        unset($submitedVehicle['tracker_password']);

        // Act
        $user = $this->addVehicleAddsVehicle();
        $data = json_decode($this->response->getContent(), true);
        $this->actingAs($user)->json('put', '/vehicles/'.$data['_id'], $submitedVehicle);

        // Assert
        $this->assertResponseOk();
        $this->seeJson($submitedVehicle);
        $this->seeJsonStructure(['_id','registration_number','mobile_phone_number','tracker_imei_number']);
        $this->seeInDatabase('vehicles',$this->bind_vehicle_Org_id($v2));
    }

    /**
 * test put driver as twilio user fails with unauthorised
 *
 * @test
 */
    public function putDriverTwilioUserFailsUnauthorised()
    {
        // Arrange
        $v2 = $this->vehicleset[1];
        $submitedVehicle = $this->bind_vehicle($v2);
        unset($submitedVehicle['tracker_password']);

        // Act
        $this->addVehicleAddsVehicle();
        $user = $this->twilioUser();
        $data = json_decode($this->response->getContent(), true);
        $this->actingAs($user)->json('put', '/vehicles/'.$data['_id'], $submitedVehicle);

        // Assert
        $this->assertResponseStatus(403);
    }

    /**
     * test put driver as operations user fails with unauthorised
     *
     * @test
     */
    public function putDriverOpsUserFailsUnauthorised()
    {
        // Arrange
        $v2 = $this->vehicleset[1];
        $user = $this->user();
        $submitedVehicle = $this->bind_vehicle($v2);
        unset($submitedVehicle['tracker_password']);
        
        // Act
        $this->addVehicleAddsVehicle();
        $data = json_decode($this->response->getContent(), true);
        $this->actingAs($user)->json('put', '/vehicles/'.$data['_id'], $submitedVehicle);

        // Assert
        $this->assertResponseStatus(403);
    }

    /**
     * test the get method route as first user works
     *
     * @test
     */
    public function getDriverAsFirstUserOk()
    {
        // Arrange
        $v = $this->vehicleset[0];
        $vehicle = $this->bind_vehicle($v);
        unset($vehicle['tracker_password']);

        // Act
        $user = $this->addVehicleAddsVehicle();
        $data = json_decode($this->response->getContent(), true);
        $this->actingAs($user)->json('get', '/vehicles/' . $data['_id']);

        // Assert
        $this->assertResponseOk();
        $this->seeJson(array_merge(['_id' => $data['_id']], $vehicle));
        $this->seeJsonStructure(['_id','registration_number','mobile_phone_number','tracker_imei_number']);
    }

    /**
     * test the get method route as twilio user fails with unauthorised
     *
     * @test
     */
    public function getDriverAsTwilioUserFailsUnauthorised()
    {
        // Arrange
        

        // Act
        $this->addVehicleAddsVehicle();
        $user = $this->twilioUser();
        $data = json_decode($this->response->getContent(), true);

        $this->actingAs($user)->json('get', '/vehicles/' . $data['_id']);

        // Assert
        $this->assertResponseStatus(403);
    }

    /**
     * test the get method route as operations user works
     *
     * @test
     */
    public function getDriverAsOpsUserOk()
    {
        // Arrange
        $v = $this->vehicleset[0];
        $vehicle = $this->bind_vehicle($v);
        unset($vehicle['tracker_password']);

        // Act
        $this->addVehicleAddsVehicle();
        $user = $this->user();
        $data = json_decode($this->response->getContent(), true);
        $this->actingAs($user)->json('get', '/vehicles/' . $data['_id']);

        // Assert
        $this->assertResponseOk();
        $this->seeJson(array_merge(['_id' => $data['_id']], $vehicle));
        $this->seeJsonStructure(['_id','registration_number','mobile_phone_number','tracker_imei_number']);
    }
    /**
     * validation vehicle rego number and Phone number are provided
     *
     * @test
     */
    public function testRequiredValidationFail()
    {
        // Arrange
        $v = $this->vehicleset[0];
        $v['registration_number'] = '';
        $v['mobile_phone_number'] = '';
        $user = $this->firstUser();

        // Act
        $this->actingAs($user)->json('post', '/vehicles', $this->bind_vehicle($v));

        // Assert
        $this->assertResponseStatus(422);
        $this->seeJson(
            [
                'registration_number' => ['We know the vehicles by their registration numbers'],
                'mobile_phone_number' => ["We need the mobile phone number used by the vehicle tracker"]
            ]
        );

    }
    /**
     * validation vehicle rego number and Phone number are provided
     *
     * @test
     */
    public function testRequiredValidationFail_put()
    {
        // Arrange
        $firstUser = $this->addVehicleAddsVehicle();
        $v = $this->vehicleset[0];
        $v['registration_number'] = '';
        $v['mobile_phone_number'] = '';

        // Act

        $this->actingAs($firstUser)->json('post', '/vehicles', $this->bind_vehicle($v));

        // Assert
        $this->assertResponseStatus(422);
        $this->seeJson(
            [
                'registration_number' => ['We know the vehicles by their registration numbers'],
                'mobile_phone_number' => ["We need the mobile phone number used by the vehicle tracker"]
            ]
        );

    }

    /**
     * tracker imei number is optional
     *
     * @test
     */
    public function testOptionalValidationPass()
    {
        // Arrange
        $v = $this->vehicleset[0];
        $v['tracker_imei_number'] = '';
        $vehicle = $this->bind_vehicle($v);
        unset($vehicle['tracker_password']);

        // Act
        $this->actingAs($this->firstUser())->json('post', '/vehicles', $vehicle);

        // Assert
        $this->assertResponseOk();
        $this->seeJson($vehicle);
        $this->seeJsonStructure(['_id','registration_number','mobile_phone_number','tracker_imei_number']);
        $this->seeInDatabase('vehicles',
            array_merge($this->bind_vehicle($v),['organisation_id'=>$this->orgset[0]['_id']]));

    }

    /**
     * validation registration and phone number, and licence are unique within an organisation
     *
     * @test
     */
    public function testUniqueRegistrationMobilePhoneImeiOrganisationValidationFail()
    {
        // Arrange
        $v = $this->vehicleset[0];
        $firstUser = $this->firstUser();

        // Act
        $this->actingAs($firstUser)->json('post', '/vehicles', $this->bind_vehicle($v));
        $this->actingAs($firstUser)->json('post', '/vehicles',  $this->bind_vehicle($v));

        // Assert
        $this->assertResponseStatus(422);
        $this->seeJson([
                'registration_number' => ['It seems you already have a vehicle in here with that registration number'],
                'mobile_phone_number' => ['Another vehicle already has that phone number'],
                'tracker_imei_number' => ['IMEI numbers are always unique but this one is being used on one of your other vehicles']
            ]
        );

    }
    /**
     * validation first and last name, phone and licence are unique ONLY within an organisation
     *
     * @test
     */
    public function testSameNamePhoneLicenceDifferentOrganisationValidationPass()
    {
        // Arrange
        $user1=$this->firstUser();
        $org2 = Organisation::where('_id',$this->orgset[1]['_id'])->firstOrFail();
        $user2 = $this->firstUser($org2);
        $v = $this->vehicleset[0];
        $vehicle = $this->bind_vehicle($v);
        unset($vehicle['tracker_password']);

        // Act
        $this->actingAs($user1)->json('post', '/vehicles', $vehicle);
        $this->actingAs($user2)->json('post', '/vehicles', $vehicle);

        // Assert
        $this->assertResponseOk();
        $this->seeJson($vehicle);
        $this->seeJsonStructure(['_id','registration_number','mobile_phone_number','tracker_imei_number']);
        $this->seeInDatabase('vehicles',array_merge($this->bind_vehicle($v),['organisation_id'=>$this->orgset[0]['_id']]));

    }


    /**
     * validation phone number is a 10 digit phone number
     *
     * @test
     */
    public function validateAlphasInPhoneNumberFail()
    {
        // Arrange
        $firstUser = $this->firstUser();
        $v = $this->vehicleset[0];
        $v['mobile_phone_number'] = '04X9140683';

        // Act

        $this->actingAs($firstUser)->json('post', '/vehicles', $this->bind_vehicle($v));

        // Assert
        $this->assertResponseStatus(422);
        $this->seeJson(
            [
                'mobile_phone_number' => ["That doesn't look like an australian phone number, it needs to have 10 digits and start with a 0 or start with +61 and have 11 digits"]
            ]
        );

    }
    /**
     * validation phone number is a 10 digit phone number
     *
     * @test
     */
    public function validateTooLongPhoneNumberFail()
    {
        // Arrange
        $v = $this->vehicleset[0];
        $v['mobile_phone_number'] = '041914068300';
        $firstUser = $this->firstUser();

        // Act
        $this->actingAs($firstUser)->json('post', '/vehicles', $this->bind_vehicle($v));

        // Assert
        $this->assertResponseStatus(422);
        $this->seeJson(
            [
                'mobile_phone_number' => ["That doesn't look like an australian phone number, it needs to have 10 digits and start with a 0 or start with +61 and have 11 digits"]
            ]
        );

    }
    /**
     * validation phone number is a 10 digit phone number
     *
     * @test
     */
    public function validateTooShortPhoneNumberFail()
    {
        // Arrange
        $v = $this->vehicleset[0];
        $v['mobile_phone_number'] = '914068300';
        $firstUser = $this->firstUser();

        // Act
        $this->actingAs($firstUser)->json('post', '/vehicles', $this->bind_vehicle($v));

        // Assert
        $this->assertResponseStatus(422);
        $this->seeJson([
                'mobile_phone_number' => ["That doesn't look like an australian phone number, it needs to have 10 digits and start with a 0 or start with +61 and have 11 digits"]
            ]
        );

    }


    /**
     * that vehicle registration number format is valid
     *
     * @test
     */
    public function validateRegoBadCharsFail()
    {
        // Arrange
        $v = $this->vehicleset[0];
        $v['registration_number'] = 'BB-66-XX';
        $firstUser = $this->firstUser();

        // Act
        $this->actingAs($firstUser)->json('post', '/vehicles',$this->bind_vehicle($v));

        // Assert
        $this->assertResponseStatus(422);
        $this->seeJson([
                'registration_number' => ['That doesn\'t look like a normal vehicle registration plate number']
            ]
        );

    }
    /**
     * that drivers licence number format is valid
     *
     * @test
     */
    public function validateShortRegoNumberFail()
    {
        // Arrange
        $v = $this->vehicleset[0];
        $v['registration_number'] = '10000';
        $firstUser = $this->firstUser();

        // Act

        $this->actingAs($firstUser)->json('post', '/vehicles',$this->bind_vehicle($v));

        // Assert
        $this->assertResponseStatus(422);
        $this->seeJson([
                'registration_number' => ['That doesn\'t look like a normal vehicle registration plate number']
            ]
        );

    }
    /**
     * that drivers licence number format is valid
     *
     * @test
     */
    public function validateLongRegoNumberFail()
    {
        // Arrange
        $v = $this->vehicleset[0];
        $v['registration_number'] = '12345678';
        $user = $this->firstUser();

        // Act

        $this->actingAs($user)->json('post', '/vehicles',$this->bind_vehicle($v));

        // Assert
        $this->assertResponseStatus(422);
        $this->seeJson([
                'registration_number' => ['That doesn\'t look like a normal vehicle registration plate number']
            ]
        );

    }
    /**
     * that IMEI number format is valid
     *
     * @test
     */
    public function validateImeiNumberShortFail()
    {
        // Arrange
        $v = $this->vehicleset[0];
        $v['tracker_imei_number'] = '12345678901234';
        $user = $this->firstUser();

        // Act

        $this->actingAs($user)->json('post', '/vehicles', $this->bind_vehicle($v));

        // Assert
        $this->assertResponseStatus(422);
        $this->seeJson([
                'tracker_imei_number' => ['That doesn\'t look like an IMEI number, please check']
            ]
        );

    }
    /**
     * that IMEI number format is valid
     *
     * @test
     */
    public function validateImeiNumberLongFail()
    {
        // Arrange
        $v = $this->vehicleset[0];
        $v['tracker_imei_number'] = '123456789012345678';
        $user = $this->firstUser();

        // Act

        $this->actingAs($user)->json('post', '/vehicles', $this->bind_vehicle($v));

        // Assert
        $this->assertResponseStatus(422);
        $this->seeJson([
                'tracker_imei_number' => ['That doesn\'t look like an IMEI number, please check']
            ]
        );

    }
    /**
     * that IMEI number format is valid
     *
     * @test
     */
    public function validateImeiNumberNonDigitFail()
    {
        // Arrange
        $v = $this->vehicleset[0];
        $v['tracker_imei_number'] = 'x234567891234567';
        $user = $this->firstUser();

        // Act

        $this->actingAs($user)->json('post', '/vehicles', $this->bind_vehicle($v));

        // Assert
        $this->assertResponseStatus(422);
        $this->seeJson([
                'tracker_imei_number' => ['That doesn\'t look like an IMEI number, please check']
            ]
        );

    }

    /**
 * test that delete vehicle as first user works
 *
 * @test
 */
    public function deleteAsFisrtUserOk(){
        // Arrange
        $v = $this->vehicleset[0];
        $user = $this->firstUser();

        // Act
        $this->actingAs($user)->json('post', '/vehicles', $this->bind_vehicle($v));
        $data = json_decode($this->response->getContent(), true);
        $this->actingAs($user)->json('delete', '/vehicles/'.$data['_id']);

        // Assert
        $this->assertResponseOk();
        $this->notSeeInDatabase('vehicles',['_id'=>$data['_id']]);

    }

    /**
     * test that delete vehicle as twilio user fails with unauthorised
     *
     * @test
     */
    public function deleteAsTwilioUserFailsUnauthorised(){
        // Arrange
        $v = $this->vehicleset[0];
        
        // Act
        $this->addVehicleAddsVehicle();
        $user = $this->twilioUser();
        $data = json_decode($this->response->getContent(), true);
        $this->actingAs($user)->json('delete', '/vehicles/'.$data['_id']);

        // Assert
        $this->assertResponseStatus(403);

    }
    /**
     * test that delete vehicle as twilio user fails with unauthorised
     *
     * @test
     */
    public function deleteAsOpsUserFailsUnauthorised(){
        // Arrange
        $v = $this->vehicleset[0];

        // Act
        $this->addVehicleAddsVehicle();
        $user=$this->user();
        $data = json_decode($this->response->getContent(), true);
        $this->actingAs($user)->json('delete', '/vehicles/'.$data['_id']);

        // Assert
        $this->assertResponseStatus(403);

    }

    /**
     * @param $v
     * @return array
     */
    protected function bind_vehicle($v)
    {
        return [
            'registration_number' => $v['registration_number'],
            'mobile_phone_number' => $v['mobile_phone_number'],
            'tracker_imei_number' => $v['tracker_imei_number'],
            'tracker_password' => $v['tracker_password']
        ];
    }

    /**
     * @param $v
     * @return array
     */
    protected function bind_vehicle_Org_id($v)
    {
        return array_merge ($this->bind_vehicle($v),['organisation_id' => $this->orgset[0]['_id']]);
    }

    /**
     * @param $v
     * @return array
     */
    protected function bind_vehicle_id($v,$id)
    {
        return array_merge(['_id' => $id],$this->bind_vehicle($v));
     }
}