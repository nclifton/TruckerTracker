<?php
/**
 *
 * @version 0.0.1: ConfigControllerDriversTest.php 4/06/2016T06:25
 * @author Clifton IT Foundries Pty Ltd
 * @link http://cliftonwebfoundry.com.au
 * @copyright Copyright (c) 2016 Clifton IT Foundries Pty Ltd. All rights Reserved
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 *
 **/

namespace TruckerTracker;

require_once __DIR__ . '/ConfigControllerTestCase.php';

class ConfigControllerDriversTest extends ConfigControllerTestCase
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
            'organisations' => $this->orgSet,
            'drivers' => [],
            'vehicles' => []
        ];
    }


    /**
     * test post driver
     *
     * @test
     */
    public function addDriver()
    {
        // Arrange
        $user = $this->firstUser();
        $d = $this->driverSet[0];

        // Act
        $this->actingAs($user)->json('post', '/drivers', $this->bind_driver($d));

        // Assert
        $this->assertResponseOk();
        $this->seeJson($this->bind_driver($d));
        $this->seeJsonStructure(['_id', 'first_name', 'last_name', 'mobile_phone_number', 'drivers_licence_number']);
        $this->seeInDatabase('drivers', array_merge($this->bind_driver($d),
            ['organisation_id' => $this->orgSet[0]['_id']]));
    }

    /**
     * test put driver
     *
     * @test
     */
    public function updateDriver()
    {
        // Arrange
        $user = $this->firstUser();
        $d1 = $this->driverSet[0];
        $d2 = $this->driverSet[1];
        // Act

        $this->actingAs($user)->json('post', '/drivers', $this->bind_driver($d1));
        $this->assertResponseOk();
        $data = json_decode($this->response->getContent(), true);

        $this->actingAs($user)->json('put', '/drivers/' . $data['_id'], $this->bind_driver($d2));

        // Assert
        $this->assertResponseOk();
        $this->seeJson($this->bind_driver($d2));
        $this->seeJsonStructure([
            '_id', 
            'first_name',
            'last_name', 
            'mobile_phone_number', 
            'drivers_licence_number'
        ]);
        $this->seeInDatabase('drivers', array_merge($this->bind_driver($d2),[
            'organisation_id' => $this->orgSet[0]['_id']
        ]));
    }

    /**
     * test the get method route
     *
     * @test
     */
    public function getDriver()
    {
        // Arrange
        $user = $this->firstUser();
        $d = $this->driverSet[0];

        // Act
        $this->actingAs($user)->json('post', '/drivers', $this->bind_driver($d));
        $this->assertResponseOk();
        
        $data = json_decode($this->response->getContent(), true);
        
        $this->actingAs($user)->json('get', '/drivers/' . $data['_id']);

        // Assert
        $this->assertResponseOk();
        $this->seeJson(array_merge(['_id' => $data['_id']], $this->bind_driver($d)));
        $this->seeJsonStructure([
            '_id',
            'first_name', 
            'last_name', 
            'mobile_phone_number', 
            'drivers_licence_number'
        ]);
        $this->seeInDatabase('drivers', array_merge($this->bind_driver($d),
            ['organisation_id' => $this->orgSet[0]['_id']]));
    }

    /**
     * validation first name last name and Phone number are provided using POST
     *
     * @test
     */
    public function testRequiredValidationFailPost()
    {
        // Arrange
        $user = $this->firstUser();
        $d = $this->driverSet[0];
        $d['first_name'] = '';
        $d['last_name'] = '';
        $d['mobile_phone_number'] = '';

        // Act
        $this->actingAs($user)->json('post', '/drivers', $this->bind_driver($d));

        // Assert
        $this->seeStatusCode(422);
        $this->seeJson([
                'first_name' => ['We do need to have first names for your drivers'],
                'last_name' => ['We do need to have last names for your drivers'],
                'mobile_phone_number' => ["We're going to need the driver's mobile phone number"]
            ]
        );

    }

    /**
     * validation first name last name and Phone number are provided using POST
     *
     * @test
     */
    public function testRequiredValidationFailPut()
    {
        // Arrange
        $user = $this->firstUser();

        $d = $this->driverSet[0];
        $this->actingAs($user)->json('post', '/drivers', $this->bind_driver($d));
        $data = json_decode($this->response->getContent(), true);

        $d['first_name'] = '';
        $d['last_name'] = '';
        $d['mobile_phone_number'] = '';

        // Act
        $this->actingAs($user)->json('put', '/drivers/' . $data['_id'], $this->bind_driver($d));

        // Assert
        $this->seeStatusCode(422);
        $this->seeJson([
                'first_name' => ['We do need to have first names for your drivers'],
                'last_name' => ['We do need to have last names for your drivers'],
                'mobile_phone_number' => ["We're going to need the driver's mobile phone number"]
            ]
        );

    }

    /**
     * drivers licence is optional
     *
     * @test
     */
    public function testOptionalValidationPass()
    {
        // Arrange
        $user = $this->firstUser();
        $d = $this->driverSet[0];
        $d['drivers_licence_number'] = '';

        // Act
        $this->actingAs($user)->json('post', '/drivers', $this->bind_driver($d));

        // Assert
        $this->assertResponseOk();
        $this->seeJson($this->bind_driver($d));
        $this->seeJsonStructure([
            '_id', 
            'first_name', 
            'last_name', 
            'mobile_phone_number', 
            'drivers_licence_number'
        ]);
        
        $this->seeInDatabase('drivers', array_merge($this->bind_driver($d), [
            'organisation_id' => $this->orgSet[0]['_id']
        ]));

    }

    /**
     * validation first and last name and phone number, and licence are unique within an organisation using POST
     *
     * @test
     */
    public function testUniqueFirstLastNameMobilePhoneLicenceOrganisationValidationFailPost()
    {
        // Arrange
        $user = $this->firstUser();
        $d = $this->driverSet[0];

        // Act
        $this->actingAs($user)->json('post', '/drivers', $this->bind_driver($d));
        $this->actingAs($user)->json('post', '/drivers', $this->bind_driver($d));

        // Assert
        $this->seeStatusCode(422);
        $this->seeJson([
                'first_name' => ['you already have a driver with that first name and last name'],
                'last_name' => ['you already have a driver with that first name and last name'],
                'mobile_phone_number' => ["Another one of your drivers has that same phone number, that's not going to work"],
                'drivers_licence_number' => ["Another one of your drivers has the same licence number, that's not allowed"]
            ]
        );

    }

    /**
     * validation first and last name and phone number, and licence are unique within an organisation using PUT
     *
     * @test
     */
    public function testUniqueFirstLastNameMobilePhoneLicenceOrganisationValidationFailPut()
    {
        // Arrange
        $user = $this->firstUser();
        $d1 = $this->driverSet[0];
        $d2 = $this->driverSet[1];

        // Act
        $this->actingAs($user)->json('post', '/drivers', $this->bind_driver($d1));
        $this->actingAs($user)->json('post', '/drivers', $this->bind_driver($d2));
        $data = json_decode($this->response->getContent(), true);
        $this->actingAs($user)->json('put', '/drivers/' . $data['_id'], $this->bind_driver($d1));
        // Assert
        $this->seeStatusCode(422);
        $this->seeJson([
                'first_name' => ['you already have a driver with that first name and last name'],
                'last_name' => ['you already have a driver with that first name and last name'],
                'mobile_phone_number' => ["Another one of your drivers has that same phone number, that's not going to work"],
                'drivers_licence_number' => ["Another one of your drivers has the same licence number, that's not allowed"]
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
        $user = $this->firstUser();
        $org2 = Organisation::where('_id',$this->orgSet[1]['_id'])->firstOrFail();
        $user2 = $this->firstUser($org2);
        $d = $this->driverSet[0];

        // Act
        $this->actingAs($user)->json('post', '/drivers', $this->bind_driver($d));
        $this->actingAs($user2)->json('post', '/drivers', $this->bind_driver($d));

        // Assert
        $this->assertResponseOk();
        $this->seeJson($this->bind_driver($d));
        $this->seeJsonStructure([
            '_id',
            'first_name',
            'last_name',
            'mobile_phone_number',
            'drivers_licence_number'
        ]);
        $this->seeInDatabase('drivers', array_merge($this->bind_driver($d), [
            'organisation_id' => $this->orgSet[0]['_id']
        ]));

    }


    /**
     * validation phone number is a 10 digit phone number using POST
     *
     * @test
     */
    public function validateAlphasInPhoneNumberFailPost()
    {
        // Arrange
        $user = $this->firstUser();
        $d = $this->driverSet[0];
        $d['mobile_phone_number'] = '04X9140683';

        // Act

        $this->actingAs($user)->json('post', '/drivers', $this->bind_driver($d));

        // Assert
        $this->seeStatusCode(422);
        $this->seeJson([
                'mobile_phone_number' => ["That doesn't look like an australian phone number, it needs to have 10 digits and start with a 0 or start with +61 and have 11 digits"]
            ]
        );

    }

    /**
     * validation phone number is a 10 digit phone number using PUT
     *
     * @test
     */
    public function validateAlphasInPhoneNumberFailPut()
    {
        // Arrange
        $user = $this->firstUser();
        $d = $this->driverSet[0];


        // Act

        $this->actingAs($user)->json('post', '/drivers', $this->bind_driver($d));
        $data = json_decode($this->response->getContent(), true);
        $d['mobile_phone_number'] = '+614X9140683';
        $this->actingAs($user)->json('put', '/drivers/' . $data['_id'], $this->bind_driver($d));

        // Assert
        $this->seeStatusCode(422);
        $this->seeJson([
                'mobile_phone_number' => ["That doesn't look like an australian phone number, it needs to have 10 digits and start with a 0 or start with +61 and have 11 digits"]
            ]
        );

    }


    /**
     * validation phone number is a 10 digit phone number using POST
     *
     * @test
     */
    public function validateTooLongPhoneNumberFailPost()
    {
        // Arrange
        $user = $this->firstUser();

        $d = $this->driverSet[0];
        $d['mobile_phone_number'] = '041914068300';

        // Act

        $this->actingAs($user)->json('post', '/drivers', $this->bind_driver($d));

        // Assert
        $this->seeStatusCode(422);
        $this->seeJson([
                'mobile_phone_number' => ["That doesn't look like an australian phone number, it needs to have 10 digits and start with a 0 or start with +61 and have 11 digits"]
            ]
        );

    }

    /**
     * validation phone number is a 10 digit phone number using PUT
     *
     * @test
     */
    public function validateTooLongPhoneNumberFailPut()
    {
        // Arrange
        $user = $this->firstUser();
        $d = $this->driverSet[0];

        // Act

        $this->actingAs($user)->json('post', '/drivers', $this->bind_driver($d));
        $data = json_decode($this->response->getContent(), true);
        $d['mobile_phone_number'] = '041914068300';
        $this->actingAs($user)->json('put', '/drivers/' . $data['_id'], $this->bind_driver($d));

        // Assert
        $this->seeStatusCode(422);
        $this->seeJson([
                'mobile_phone_number' => ["That doesn't look like an australian phone number, it needs to have 10 digits and start with a 0 or start with +61 and have 11 digits"]
            ]
        );

    }

    /**
     * validation phone number is a 10 digit phone number using POST
     *
     * @test
     */
    public function validateTooShortPhoneNumberFailPost()
    {
        // Arrange
        $user = $this->firstUser();
        $d = $this->driverSet[0];
        $d['mobile_phone_number'] = '914068300';

        // Act

        $this->actingAs($user)->json('post', '/drivers', $this->bind_driver($d));

        // Assert
        $this->seeStatusCode(422);
        $this->seeJson([
                'mobile_phone_number' => ["That doesn't look like an australian phone number, it needs to have 10 digits and start with a 0 or start with +61 and have 11 digits"]
            ]
        );

    }

    /**
     * validation phone number is a 10 digit phone number using PUT
     *
     * @test
     */
    public function validateTooShortPhoneNumberFailPut()
    {
        // Arrange
        $user = $this->firstUser();

        $d = $this->driverSet[0];


        // Act

        $this->actingAs($user)->json('post', '/drivers', $this->bind_driver($d));
        $d['mobile_phone_number'] = '914068300';
        $data = json_decode($this->response->getContent(), true);
        $this->actingAs($user)->json('put', '/drivers/' . $data['_id'], $this->bind_driver($d));

        // Assert
        $this->seeStatusCode(422);
        $this->seeJson([
                'mobile_phone_number' => ["That doesn't look like an australian phone number, it needs to have 10 digits and start with a 0 or start with +61 and have 11 digits"]
            ]
        );

    }


    /**
     * that drivers licence number format is valid using POST
     *
     * @test
     */
    public function validateTooShortLicenceNumberFailPost()
    {
        // Arrange
        $user = $this->firstUser();
        $d = $this->driverSet[0];
        $d['drivers_licence_number'] = '300';

        // Act

        $this->actingAs($user)->json('post', '/drivers', $this->bind_driver($d));

        // Assert
        $this->seeStatusCode(422);
        $this->seeJson([
                'drivers_licence_number' => ["That drivers licence number looks odd, please check, should be 4 to 9 alpha numeric"]
            ]
        );

    }

    /**
     * that drivers licence number format is valid using PUT
     *
     * @test
     */
    public function validateTooShortLicenceNumberFailPut()
    {
        // Arrange
        $user = $this->firstUser();
        $d = $this->driverSet[0];


        // Act

        $this->actingAs($user)->json('post', '/drivers', $this->bind_driver($d));
        $d['drivers_licence_number'] = '300';
        $data = json_decode($this->response->getContent(), true);
        $this->actingAs($user)->json('put', '/drivers/' . $data['_id'], $this->bind_driver($d));

        // Assert
        $this->seeStatusCode(422);
        $this->seeJson([
                'drivers_licence_number' => ["That drivers licence number looks odd, please check, should be 4 to 9 alpha numeric"]
            ]
        );

    }


    /**
     * that drivers licence number format is valid using POST
     *
     * @test
     */
    public function validateAlphasInPos3or4LicenceNumberFailPost()
    {
        // Arrange
        $user = $this->firstUser();
        $d = $this->driverSet[0];
        $d['drivers_licence_number'] = '100A00300';

        // Act

        $this->actingAs($user)->json('post', '/drivers', $this->bind_driver($d));

        // Assert
        $this->seeStatusCode(422);
        $this->seeJson([
                'drivers_licence_number' => ["That drivers licence number looks odd, please check, should be 4 to 9 alpha numeric"]
            ]
        );

    }

    /**
     * that drivers licence number format is valid using PUT
     *
     * @test
     */
    public function validateAlphasInPos3or4LicenceNumberFailPut()
    {
        // Arrange
        $user = $this->firstUser();
        $d = $this->driverSet[0];


        // Act

        $this->actingAs($user)->json('post', '/drivers', $this->bind_driver($d));
        $data = json_decode($this->response->getContent(), true);
        $d['drivers_licence_number'] = '100A00300';
        $this->actingAs($user)->json('put', '/drivers/' . $data['_id'], $this->bind_driver($d));

        // Assert
        $this->seeStatusCode(422);
        $this->seeJson([
                'drivers_licence_number' => ["That drivers licence number looks odd, please check, should be 4 to 9 alpha numeric"]
            ]
        );

    }

    /**
     * that drivers licence number format is valid
     *
     * @test
     */
    public function validateAlphasInPos3or4LicenceNumberPass()
    {
        // Arrange
        $user = $this->firstUser();
        $d = $this->driverSet[0];
        $d['drivers_licence_number'] = '0B11E0000';

        // Act

        $this->actingAs($user)->json('post', '/drivers', $this->bind_driver($d));

        // Assert
        $this->assertResponseOk();
        $this->seeJson($this->bind_driver($d));
        $this->seeJsonStructure([
            '_id',
            'first_name',
            'last_name',
            'mobile_phone_number',
            'drivers_licence_number'
        ]);
        $this->seeInDatabase('drivers', array_merge($this->bind_driver($d), [
            'organisation_id' => $this->orgSet[0]['_id']
        ]));

    }

    /**
     * that drivers licence number format is valid using POST
     *
     * @test
     */
    public function validateAtLeast4NumericLicenceNumberFailPost()
    {
        // Arrange
        $user = $this->firstUser();
        $d = $this->driverSet[0];
        $d['drivers_licence_number'] = 'AA00AAA0A';

        // Act

        $this->actingAs($user)->json('post', '/drivers', $this->bind_driver($d));

        // Assert
        $this->seeStatusCode(422);
        $this->seeJson([
                'drivers_licence_number' => ["That drivers licence number looks odd, please check, should be 4 to 9 alpha numeric"]
            ]
        );

    }

    /**
     * that drivers licence number format is valid using PUT
     *
     * @test
     */
    public function validateAtLeast4NumericLicenceNumberFailPut()
    {
        // Arrange
        $user = $this->firstUser();
        $d = $this->driverSet[0];


        // Act

        $this->actingAs($user)->json('post', '/drivers', $this->bind_driver($d));
        $data = json_decode($this->response->getContent(), true);
        $d['drivers_licence_number'] = 'AA00AAA0A';
        $this->actingAs($user)->json('put', '/drivers/' . $data['_id'], $this->bind_driver($d));

        // Assert
        $this->seeStatusCode(422);
        $this->seeJson([
                'drivers_licence_number' => ["That drivers licence number looks odd, please check, should be 4 to 9 alpha numeric"]
            ]
        );

    }

    /**
     * that drivers licence number format is valid using POST
     *
     * @test
     */
    public function validateNoMoreThan2AlphaNumberFailPost()
    {
        // Arrange
        $user = $this->firstUser();
        $d = $this->driverSet[0];
        $d['drivers_licence_number'] = 'A00000A0A';

        // Act

        $this->actingAs($user)->json('post', '/drivers', $this->bind_driver($d));

        // Assert
        $this->seeStatusCode(422);
        $this->seeJson([
                'drivers_licence_number' => ["That drivers licence number looks odd, please check, should be 4 to 9 alpha numeric"]
            ]
        );

    }

    /**
     * that drivers licence number format is valid using PUT
     *
     * @test
     */
    public function validateNoMoreThan2AlphaNumberFailPut()
    {
        // Arrange
        $user = $this->firstUser();
        $d = $this->driverSet[0];


        // Act

        $this->actingAs($user)->json('post', '/drivers', $this->bind_driver($d));
        $data = json_decode($this->response->getContent(), true);
        $d['drivers_licence_number'] = 'A00000A0A';
        $this->actingAs($user)->json('put', '/drivers/' . $data['_id'], $this->bind_driver($d));

        // Assert
        $this->seeStatusCode(422);
        $this->seeJson([
                'drivers_licence_number' => ["That drivers licence number looks odd, please check, should be 4 to 9 alpha numeric"]
            ]
        );

    }

    /**
     * test that delete driver works
     *
     * @test
     */
    public function deletesDriver()
    {
        // Arrange
        $user = $this->firstUser();
        $d = $this->driverSet[0];

        $this->actingAs($user)->json('post', '/drivers', $this->bind_driver($d));
        $data = json_decode($this->response->getContent(), true);

        // Act

        $this->actingAs($user)->json('delete', '/drivers/' . $data['_id']);

        $this->notSeeInDatabase('drivers', ['_id' => $data['_id']]);


    }

    /**
     * @param $d
     * @return array
     */
    protected function bind_driver($d)
    {
        return [
            'first_name' => $d['first_name'],
            'last_name' => $d['last_name'],
            'mobile_phone_number' => $d['mobile_phone_number'],
            'drivers_licence_number' => $d['drivers_licence_number']
        ];
    }

}