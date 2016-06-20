<?php

namespace TruckerTracker;

use DB;

require_once __DIR__ . '/ConfigControllerTestCase.php';

class ConfigControllerOrganisationsTest extends ConfigControllerTestCase
{
    private $longname = 'a very long organisation name has to be more than 128 character long like this one when I add a lot fo words to the organisation name';

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
     * "GET" organisation as first user.
     *
     * @return void
     *
     * @test
     */
    public function getOrganisationFirstUser()
    {
        // Arrange
        $user = $this->firstUser();
        $tUser = $this->twilioUser();
        $this->user();
        $org = $this->orgset[0];

        // Act
        $this->actingAs($user)->json('get', '/organisation/' . $org['_id']);

        // Assert
        $this->assertResponseOk();
        $data = json_decode($this->response->getContent(), true);
        $this->seeJsonStructure([
            '_id',
            'name',
            'timezone',
            'datetime_format',
            'twilio_account_sid',
            'twilio_auth_token',
            'twilio_phone_number',
            'twilio_user_password',
            'twilio_inbound_message_request_url',
            'twilio_outbound_message_status_callback_url',
            'auto_reply',
            'users' => [
                [
                    '_id',
                    'name',
                    'email'
                ]
            ]
        ]);
        $host = env('SERVER_DOMAIN_NAME','example.com');
        $this->seeJson([
            '_id' => $org['_id'],
            'name' => $org['name'],
            'twilio_inbound_message_request_url' => 
                'http://'.$tUser->username.':'.$org['twilio_user_password']. '@' . $host . '/incoming/message',
            'twilio_outbound_message_status_callback_url' =>
                'http://'.$tUser->username.':'.$org['twilio_user_password'].'@' . $host . '/incoming/message/status',
        ]);

    }

    /**
     * "GET" another organisation as first organisation first user.
     *
     * @return void
     *
     * @test
     */
    public function getOtherOrganisationFirstUserFails()
    {
        // Arrange
        $user = $this->firstUser();
        $org = $this->orgset[1];

        // Act

        $this->actingAs($user)->json('get', '/organisation/' . $org['_id']);

        // Assert
        $this->assertResponseStatus(403);

    }

    /**
     * "GET" organisation as twilio user - should fail
     *
     * @return void
     *
     * @test
     */
    public function getOrganisationAsTwilioUserFails()
    {
        // Arrange
        $user = $this->twilioUser();
        $org = $this->orgset[0];

        // Act

        $this->actingAs($user)->json('get', '/organisation/' . $org['_id']);

        // Assert
        $this->assertResponseStatus(403);

    }


    /**
     * "GET" other organisation as first organisation twilio user - should fail
     *
     * @return void
     *
     * @test
     */
    public function getOtherOrganisationAsTwilioUserFails()
    {
        // Arrange
        $user = $this->twilioUser();
        $org = $this->orgset[1];

        // Act

        $this->actingAs($user)->json('get', '/organisation/' . $org['_id']);

        // Assert
        $this->assertResponseStatus(403);

    }

    /**
     * "GET" organisation as operations user - should fail
     *
     * @return void
     *
     * @test
     */
    public function getOrganisationAsOpsUserFails()
    {
        // Arrange
        $user = $this->user();
        $org = $this->orgset[0];

        // Act

        $this->actingAs($user)->json('get', '/organisation/' . $org['_id']);

        // Assert
        $this->assertResponseStatus(403);

    }

    /**
     * "GET" other organisation as first organisation operations user - should fail
     *
     * @return void
     *
     * @test
     */
    public function getOtherOrganisationAsOpsUserFails()
    {
        // Arrange
        $user = $this->user();
        $org = $this->orgset[1];

        // Act

        $this->actingAs($user)->json('get', '/organisation/' . $org['_id']);

        // Assert
        $this->assertResponseStatus(403);

    }

    /**
     * "POST" organisation.
     * - create organisation
     * - makes user the organisation's first user
     * - creates the organisation's twilio user
     * - both users details available in JSON response
     * - twilio user password is revealed in the JSON response
     * - tuilio user email same as first user
     * - auto_reply false
     *
     * @return void
     *
     * @test
     */
    public function createNewOrganisation()
    {
        // Arrange
        $user = $this->user();
        $twilioUserName = 'twiliouser';
        $twilioEmail = preg_replace('/^[^@]*(.*)/', $twilioUserName . '$1', $user->email);

        $org = $this->orgset[0];
        $this->getMongoConnection()->collection('organisations')
            ->remove(['_id' => $org['_id']]);

        // Act

        $this->actingAs($user)->json('post', '/organisation', [
            'name' => $org['name']
        ]);

        // Assert
        $this->assertResponseOk();

        $this->seeJson([
            'name' => $org['name'],
            'auto_reply' => false,
        ]);
        $data = json_decode($this->response->getContent(), true);
        $this->seeJsonStructure(
            [
                '_id',
                'name',
                'twilio_user_password'
            ]);
        $this->seeInDatabase('organisations',
            [
                ['name', $org['name']],
                ['first_user_id', $user->_id],
                ['twilio_user_id', '<>', null]
            ]);
        $this->seeInDatabase('users', ['_id' => $user->_id, 'organisation_id' => $data['_id']]);
        $this->seeInDatabase('users', ['email' => $twilioEmail, 'organisation_id' => $data['_id']]);

    }

    /**
     * "PUT" organisation. - save changes to organisation
     *
     * @return void
     *
     * @test
     */
    public function saveChangesToOrganisationAsFirstUser()
    {
        // Arrange
        $user = $this->firstUser();
        $this->twilioUser(); // the database needs it
        $org = $this->orgset[0];

        // Act

        $someOtherName = 'Some Other Name';
        $this->actingAs($user)->json('put', '/organisation/' . $org['_id'], [
            'name' => $someOtherName
        ]);

        // Assert
        $this->assertResponseOk();
        $this->seeJson([
            'name' => $someOtherName
        ]);
        $this->seeJsonStructure(['_id', 'name']);
        $this->seeInDatabase('organisations', ['name' => $someOtherName]);

    }

    /**
     * name supplied validation
     * @test
     */
    public function organisationNameSuppliedValidation()
    {
        // Arrange
        $user = $this->firstUser();

        // Act

        $noName = '';
        $this->actingAs($user)->json('post', '/organisation', [
            'name' => $noName
        ]);

        // Assert
        $this->seeStatusCode(422);
        $this->seeJson(['name' => ['We do need a name for your organisation']]
        );
    }

    /**
     * name unique validation
     * @test
     */
    public function organisationNameNoUniqueValidation()
    {
        // Arrange
        $user = $this->firstUser();
        $org = $this->orgset[0];

        // Act

        $noName = '';
        $this->actingAs($user)->json('post', '/organisation', [
            'name' => $org['name']
        ]);

        // Assert
        $this->assertResponseOk();

    }

    /**
     * name length validation
     * @test
     */
    public function organisationNameLengthValidation()
    {
        // Arrange
        $user = $this->firstUser();

        // Act

        $this->actingAs($user)->json('post', '/organisation', [
            'name' => $this->longname
        ]);

        // Assert
        $this->seeStatusCode(422);
        $this->seeJson(['name' => ['That name for you organisation is too long, make it less than 128']]
        );
    }

    /**
     * name supplied validation on put method
     * @test
     */
    public function organisationNameSuppliedValidationPut()
    {
        // Arrange
        $user = $this->firstUser();
        $org = $this->orgset[0];

        // Act

        $noName = '';
        $this->actingAs($user)->json('put', '/organisation/' . $org['_id'], [
            'name' => $noName
        ]);

        // Assert
        $this->seeStatusCode(422);
        $this->seeJson(['name' => ['We do need a name for your organisation']]
        );
    }

    /**
     * name not unique validation - is ok
     * @test
     */
    public function organisationNameUniqueValidationPut()
    {
        // Arrange
        $user = $this->firstUser();
        $this->twilioUser();
        $org = $this->orgset[0];

        // Act

        $this->actingAs($user)->json('post', '/organisation', [
            'name' => 'Some Other Name'
        ]);
        $this->actingAs($user)->json('put', '/organisation/' . $org['_id'], [
            'name' => 'Some Other Name'
        ]);

        // Assert
        $this->assertResponseOk();

    }

    /**
     * name unique validation
     * @test
     */
    public function organisationNameLengthValidationPut()
    {
        // Arrange
        $user = $this->firstUser();

        // Act

        $this->actingAs($user)->json('post', '/organisation', [
            'name' => $this->longname
        ]);


        // Assert
        $this->seeStatusCode(422);
        $this->seeJson(['name' => ['That name for you organisation is too long, make it less than 128']]
        );
    }

    /**
     *
     * creates including twilio_account_sid
     *
     * @test
     */
    public function createNewOrganisationIncludingTwilioAccountSid()
    {
        $this->assertCreateOrganisationWithOptionalProperty('twilio_account_sid');
    }

    /**
     *
     * updates twilio_account_sid
     *
     * @test
     */
    public function updateOrganisationIncludingTwilioAccountSid()
    {
        $this->assertUpdateOptionalProperty('twilio_account_sid');
    }

    /**
     *
     * validates twilio_account_sid
     *
     * @test
     */
    public function validateTwilioAccountSidFail()
    {
        $this->assertValidation(
            'twilio_account_sid',
            '12456',
            'That does not match the pattern of a Twilio Account SID, please check');
    }

    /**
     *
     *  validates twilio_auth_token
     *
     * @test
     */
    public function validateTwilioAuthTokenFail()
    {
        $this->assertValidation(
            'twilio_auth_token',
            '12456',
            'That does not match the pattern of a Twilio Authentication Token, please check');
    }

    /**
     *
     *  stores twilio_auth_token
     *
     * @test
     */
    public function createNewOrganisationIncludingTwilioAuthToken()
    {
        $this->assertCreateOrganisationWithOptionalProperty('twilio_auth_token');
    }

    /**
     *
     *  updates twilio_auth_token
     *
     * @test
     */
    public function updateOrganisationIncludingTwilioAuthToken()
    {
        $this->assertUpdateOptionalProperty('twilio_auth_token');
    }

    /**
     *
     *  validates twilio_phone_number
     *
     * @test
     */
    public function validateTwilioPhoneNumberFail()
    {
        $this->assertValidation(
            'twilio_phone_number',
            '12456',
            'That doesn\'t look like an australian phone number, it needs to have 10 digits and start with a 0 or start with +61 and have 11 digits');
    }

    /**
     *
     *  creates with twilio_phone_number
     *
     * @test
     */
    public function createNewOrganisationIncludingTwilioPhoneNumber()
    {
        $this->assertCreateOrganisationWithOptionalProperty('twilio_phone_number', '+61419140683');
    }

    /**
     *
     *  updates twilio_phone_number and converts to international format
     *
     * @test
     */
    public function updateOrganisationIncludingTwilioPhoneNumber()
    {
        $this->assertUpdateOptionalProperty('twilio_phone_number', '0419140683', '+61419140683');
    }

    /**
     *
     * creates organisation with timezone
     *
     * @test
     */
    public function createNewOrganisationIncludingTimezone()
    {
        $this->assertCreateOrganisationWithOptionalProperty('timezone');
    }

    /**
     *
     *  validates timezone
     *
     * @test
     */
    public function validateTimezoneFail()
    {
        $this->assertValidation(
            'timezone',
            'junk',
            'That doesn\'t look like one of our valid timezone names. Should be something like Australia/Sydney');
    }

    /**
     *
     *  updates timezone
     *
     * @test
     */
    public function updateOrganisationIncludingTimezone()
    {
        $this->assertUpdateOptionalProperty('timezone');
    }


    /**
     *
     * creates organisation with datetime_format
     *
     * @test
     */
    // TODO createNewOrganisationIncludingDatetimeFormat

    /**
     *
     *  validates datetime_format
     *
     * @test
     */
    // TODO validateDatetimeFormatFail

    /**
     *
     *  updates datetime_format
     *
     * @test
     */
    // TODO updateOrganisationIncludingDatetimeFormat


    /**
     *
     *   auto_reply
     *
     * @test
     */
    public function updateOrganisationAutoReplyFalse()
    {
        $this->assertUpdateOptionalProperty('auto_reply', false);
    }

    
    /**
     * @param $propertyName
     * @param null $value
     * @param null $storedValue
     */
    protected function assertUpdateOptionalProperty($propertyName, $value = null, $storedValue = null)
    {
        // Arrange
        $user = $this->firstUser();
        $org = $this->orgset[0];
        $value = $value ?: $org[$propertyName];
        $storedValue = $storedValue ?: $value;
        DB::collection('organisations')
            ->where('_id', $org['_id'])
            ->update([$propertyName => is_bool($value) ? !$value : '']);

        // Act
        $this->actingAs($user)->json('put', '/organisation/' . $org['_id'], [
            'name' => $org['name'],
            $propertyName => $storedValue
        ]);

        // Assert
        $this->assertResponseOk();
        $this->seeJson([
            'name' => $org['name'],
            $propertyName => $storedValue
        ]);
        $data = json_decode($this->response->getContent(), true);
        $this->seeJsonStructure(
            [
                '_id',
                'name',
                $propertyName
            ]);
        $this->seeInDatabase('organisations',
            [
                '_id' => $org['_id'],
                'name' => $org['name'],
                $propertyName => $storedValue
            ]);
    }

    /**
     * @param $propertyName
     * @param null $value
     */
    protected function assertCreateOrganisationWithOptionalProperty($propertyName, $value = null, $storedValue = null)
    {
        // Arrange
        $user = $this->user();
        $org = $this->orgset[0];
        $value = $value ?: $org[$propertyName];
        $storedValue = $storedValue ?: $value;
        $this->getMongoConnection()->collection('organisations')
            ->remove(['_id' => $org['_id']]);

        // Act

        $this->actingAs($user)->json('post', '/organisation', [
            'name' => $org['name'],
            $propertyName => $value
        ]);

        // Assert
        $this->assertResponseOk();
        $this->seeJson([
            'name' => $org['name'],
            $propertyName => $storedValue
        ]);
        $data = json_decode($this->response->getContent(), true);
        $this->seeJsonStructure(
            [
                '_id',
                'name',
                $propertyName
            ]);
        $this->seeInDatabase('organisations',
            [
                'name' => $org['name'],
                $propertyName => $storedValue
            ]);
    }

    /**
     * @param $propertyName
     * @param $badValue
     * @param $text
     */
    protected function assertValidation($propertyName, $badValue, $text)
    {
        // Arrange
        $user = $this->user();
        $org = $this->orgset[0];
        $this->getMongoConnection()->collection('organisations')
            ->remove(['_id' => $org['_id']]);

        // Act

        $this->actingAs($user)->json('post', '/organisation', [
            'name' => $org['name'],
            $propertyName => $badValue
        ]);

        // Assert
        $this->seeStatusCode(422);
        $this->seeJson([$propertyName => [$text]]);
    }

}
