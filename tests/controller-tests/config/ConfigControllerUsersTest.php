<?php
/**
 *
 * @version 0.0.1: ConfigControllerUsersTest.php 18/06/2016T01:19
 * @author Clifton IT Foundries Pty Ltd
 * @link http://cliftonwebfoundry.com.au
 * @copyright Copyright (c) 2016 Clifton IT Foundries Pty Ltd. All rights Reserved
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 *
 **/


namespace TruckerTracker;

require_once __DIR__ . '/ConfigControllerTestCase.php';

class ConfigControllerUsersTest extends ConfigControllerTestCase
{
    /**
     * @before
     */
    public function setUp()
    {
        parent::setUp();
    }

    /**
     *
     * create new organisation operations user
     *
     * @test
     */
    public function createOperationsUser()
    {
        $user = $this->firstUser();
        $org = $this->orgSet[0];
        $name = 'op1';
        $email = 'op1@example.com';
        $password = 'mstgpwd1';

        // Act
        $this->actingAs($user)->json('post', '/user', [
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'password_confirmation' => $password
        ]);

        // Assert
        $this->assertResponseOk();
        $this->seeJson([
            'name' => $name,
            'email' => $email,
            'organisation_id' => $org['_id']
        ]);

        $this->seeJsonStructure(
            [
                'name',
                'email'
            ]);
        $this->seeInDatabase('users',
            [
                'name' => $name,
                'email' => $email,
                'organisation_id' => $org['_id']
            ]);

    }

    /**
     *
     * update an  operations user - all fields filled
     *
     * @test
     */
    public function updateAnOperationsUser()
    {
        $fuser = $this->firstUser();
        $user = $this->user();
        $org = $this->orgSet[0];
        $name = 'op1x';
        $email = 'op1x@example.com';
        $password = 'mstgpwd2';

        // Act
        $this->actingAs($fuser)->json('put', '/user/' . $user->_id,
            [
                'name' => $name,
                'email' => $email,
                'password' => $password,
                'password_confirmation' => $password
            ]);

        // Assert
        $this->assertResponseOk();
        $this->seeJson([
            'name' => $name,
            'email' => $email
        ]);

        $this->seeJsonStructure(
            [
                '_id',
                'name',
                'email',
                'organisation_id'
            ]);
        $this->seeInDatabase('users',
            [
                '_id' => $user->_id,
                'name' => $name,
                'email' => $email,
                'organisation_id' => $org['_id']
            ]);

    }


    /**
     *
     * update delete an organisation operations user
     *
     * @test
     */
    public function deleteAnOperationsUser()
    {
        $fuser = $this->firstuser();
        $user = $this->user();
        $org = $this->orgSet[0];

        // Act
        $this->actingAs($fuser)->json('delete', '/user/' . $user->_id, []);

        // Assert some more
        $this->assertResponseOk();
        $this->notSeeInDatabase('users',
            [
                '_id' => $user->_id
            ]);
    }

    /*
     * validation
     * 
     *              'name' => 'required|max:255',
     *              'email' => 'required|email|max:255|unique:users',
     *              'password' => 'required|min:6|confirmed',
     * 
     */
    
    /**
     * Validate name not blank when using method POST
     * 
     * @test
     */
    public function validatesNameRequiredUsingPost()
    {
        //Arrange
        $login = $this->firstUser();
        $org = $this->orgSet[0];
        $user = $this->loginUserSet[0];
        
        // Act
        $this->actingAs($login)->json('post','/user',
            [
                'name' => '',
                'email' => $user['email'],
                'password' => $user['password'],
                'password_confirmation' => $user['password']
            ]);   
            

        // Assert
        $this->seeStatusCode(422);
        $this->seeJson(
            [
                 'name' => ['The name field is required.']
            ]
         );
    }
    /**
     * Validate name length method PUT
     *
     * @test
     */
    public function validatesNameLengthUsingPost()
    {
        //Arrange
        $login = $this->firstUser();
        $this->twilioUser();
        $user = $this->user();
        $xUser = $this->loginUserSet[0];
        $org = $this->orgSet[0];

        // Act
        $this->actingAs($login)->json('put','/user/'.$user->_id,
            [
                'name' => str_repeat('x',256),
                'email' => $xUser['email'],
                'password' => $xUser['password'],
                'password_confirmation' => $xUser['password']
            ]);


        // Assert
        $this->seeStatusCode(422);
        $data = json_decode($this->response->getContent(), true);
        $this->seeJson(
            [
                'name' => ['The name may not be greater than 255 characters.']
            ]
        );
    }
    /**
     * Validate email required
     *
     * @test
     */
    public function validatesEmailRequiredUsingPost()
    {
        //Arrange
        $login = $this->firstUser();
        $this->twilioUser();
        $user = $this->user();
        $xUser = $this->loginUserSet[0];
        $org = $this->orgSet[0];

        // Act
        $this->actingAs($login)->json('put','/user/'.$user->_id,
            [
                'name' => $xUser['name'],
                'email' => '',
                'password' => $xUser['password'],
                'password_confirmation' => $xUser['password']
            ]);


        // Assert
        $this->seeStatusCode(422);
        $data = json_decode($this->response->getContent(), true);
        $this->seeJson(
            [
                'email' => ['The email field is required.']
            ]
        );
    }
    /**
     * Validate email valid format
     *
     * @test
     */
    public function validatesEmailFormat()
    {
        //Arrange
        $login = $this->firstUser();
        $this->twilioUser();
        $user = $this->user();
        $xUser = $this->loginUserSet[0];
        $org = $this->orgSet[0];

        // Act
        $this->actingAs($login)->json('put','/user/'.$user->_id,
            [
                'name' => $xUser['name'],
                'email' => 'something that\'s not an email address',
                'password' => $xUser['password'],
                'password_confirmation' => $xUser['password']
            ]);

        // Assert
        $this->seeStatusCode(422);
        $data = json_decode($this->response->getContent(), true);
        $this->seeJson(
            [
                'email' => ['The email must be a valid email address.']
            ]
        );
    }

    /**
     *
     * Validate email unique
     *
     * @test
     */
    public function validatesEmailUnique()
    {
        //Arrange
        $login = $this->firstUser();
        $this->twilioUser();
        $user = $this->user();
        $xUser = $this->loginUserSet[0];
        $org = $this->orgSet[0];

        // Act
        $this->actingAs($login)->json('put','/user/'.$user->_id,
            [
                'name' => $xUser['name'],
                'email' => $login->email,
                'password' => $xUser['password'],
                'password_confirmation' => $xUser['password']
            ]);

        // Assert
        $this->seeStatusCode(422);
        $data = json_decode($this->response->getContent(), true);
        $this->seeJson(
            [
                'email' => ['The email has already been taken.']
            ]
        );
    }
    /**
     *
     * allows email unchanged, name changed, password no change (not included in json)
     *
     * @test
     */
    public function allowEmailUnchanged()
    {
        //Arrange
        $login = $this->firstUser();
        $this->twilioUser();
        $user = $this->user();
        $xUser = $this->loginUserSet[0];
        $org = $this->orgSet[0];

        // Act
        $this->actingAs($login)->json('put','/user/'.$user->_id,
            [
                'name' => $xUser['name'],
                'email' => $user->email
            ]);

        // Assert
        $data = json_decode($this->response->getContent(), true);
        $this->assertResponseOk();

    }
    /**
     *
     * Validate password required for post
     * - supplied blank
     *
     * @test
     */
    public function validatesPasswordRequired()
    {
        //Arrange
        $login = $this->firstUser();
        $this->twilioUser();
        $user = $this->user();
        $xUser = $this->loginUserSet[0];
        $org = $this->orgSet[0];

        // Act
        $this->actingAs($login)->json('post','/user',
            [
                'name' => $xUser['name'],
                'email' => $xUser['email'],
                'password' => '',
                'password_confirmation' => ''
            ]);

        // Assert
        $this->seeStatusCode(422);
        $this->seeJson(
            [
                'password' => ['The password field is required.']
            ]
        );
    }

    /**
     * allow blank password (and confirmation) for put
     *
     * @test
     */
    public function allow_Blank_Password_and_confirm_For_Update()
    {
        //Arrange
        $login = $this->firstUser();
        $this->twilioUser();
        $user = $this->user();
        $xUser = $this->loginUserSet[0];
        $org = $this->orgSet[0];

        // Act
        $this->actingAs($login)->json('put','/user/'.$user->_id,
            [
                'name' => $xUser['name'],
                'email' => $xUser['email'],
                'password' => '',
                'password_confirmation' => ''
            ]);

        // Assert
        $this->assertResponseOk();

    }

    /**
     *
     * Validate password longer than 6
     *
     * @test
     */
    public function validatesPasswordLength()
    {
        //Arrange
        $login = $this->firstUser();
        $this->twilioUser();
        $user = $this->user();
        $xUser = $this->loginUserSet[0];
        $org = $this->orgSet[0];

        // Act
        $this->actingAs($login)->json('put','/user/'.$user->_id,
            [
                'name' => $xUser['name'],
                'email' => $xUser['email'],
                'password' => 'sd',
                'password_confirmation' => 'sd'
            ]);

        // Assert
        $this->seeStatusCode(422);
        $data = json_decode($this->response->getContent(), true);
        $this->seeJson(
            [
                'password' => ['The password must be at least 6 characters.']
            ]
        );
    }
    /**
     *
     * Validate password confirmed
     *
     * @test
     */
    public function validatesPasswordConfirmed()
    {
        //Arrange
        $login = $this->firstUser();
        $this->twilioUser();
        $user = $this->user();
        $xUser = $this->loginUserSet[0];
        $org = $this->orgSet[0];

        // Act
        $this->actingAs($login)->json('put','/user/'.$user->_id,
            [
                'name' => $xUser['name'],
                'email' => $xUser['email'],
                'password' => $xUser['email'],
                'password_confirmation' => ''
            ]);

        // Assert
        $this->seeStatusCode(422);
        $data = json_decode($this->response->getContent(), true);
        $this->seeJson(
            [
                'password' => ['The password confirmation does not match.']
            ]
        );
    }

    /**
     * get the user
     *
     * @test
     */
    public function getUser()
    {
        //Arrange
        $login = $this->firstUser();
        $this->twilioUser();
        $user = $this->user();
        $org = $this->orgSet[0];

        // Act
        $this->actingAs($login)->json('get','/user/'.$user->_id);
        // Assert
        $this->assertResponseOk();
        $data = json_decode($this->response->getContent(), true);
        $this->assertResponseOk();
        $this->seeJson([
            'name' => $user->name,
            'email' => $user->email
        ]);


    }
}
