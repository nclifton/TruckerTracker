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
        $oUser = $this->user();
        $org = $this->orgSet[0];
        $urlParts = [
            'scheme' => config('app.external_scheme'),
            'host' => config('app.external_host'),
            'port' => config('app.external_port'),
            'user' => $tUser->username,
            'pass' => $org['twilio_user_password'],
            'path' => '/incoming/message'
        ];
        $expected_twilio_inbound_message_request_url = http_build_url('', $urlParts);
        $urlParts['path'] = '/incoming/message/status';
        $expected_twilio_outbound_message_status_callback_url = http_build_url('', $urlParts);

            //'http://' . $tUser->username . ':' . $org['twilio_user_password'] . '@' . $host . '/incoming/message/status';

        // Act
        $this->actingAs($user)->json('get', '/organisation/' . $org['_id']);

        // Assert
        $this->assertResponseOk();
        $data = json_decode($this->response->getContent(), true);
        $this->seeJsonStructure([
            '_id',
            'name',
            'timezone',
            'hour12',
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
        $this->seeJson([
            '_id' => $org['_id'],
            'name' => $org['name'],
            'twilio_inbound_message_request_url' =>
                $expected_twilio_inbound_message_request_url,
            'twilio_outbound_message_status_callback_url' =>
                $expected_twilio_outbound_message_status_callback_url,
        ]);
        $this->seeJson([
            'name' => $oUser->name,
            'email' => $oUser->email
        ]);
        $this->notSeeJason([
            'name' => $user->name,
            'email' => $user->email
        ]);
        $this->notSeeJason([
            'name' => $tUser->name,
            'email' => $tUser->email
        ]);
/*
 *
 * Unable to find JSON fragment
 * [
 * "twilio_inbound_message_request_url":"http:\/\/5c1f63bd5fd7b71506ee2de36bdd2027:1ad19bda8aff34050549c306e2d7b961@mcsweeneytg.com.au\/incoming\/message"]
 * "twilio_inbound_message_request_url":"http:\/\/5c1f63bd5fd7b71506ee2de36bdd2027:1ad19bda8aff34050549c306e2d7b961@mcsweeneytg.com.au:8000\/incoming\/message",
 * within
 * [{"_id":"10001",
 * "auto_reply":true,
 * "datetime_format":"H:i:s d\/m\/y",
 * "name":"McSweeney Transport Group",
 * "timezone":"Australia\/Sydney",
 * "twilio_account_sid":"AC392e8d8bc564eb45ea67cc0f3a8ebf3c",
 * "twilio_auth_token":"36c8ee5499df1e116aa53b1ee05ca5fa",
 * "twilio_outbound_message_status_callback_url":"http:\/\/5c1f63bd5fd7b71506ee2de36bdd2027:1ad19bda8aff34050549c306e2d7b961@mcsweeneytg.com.au:8000\/incoming\/message\/status",
 * "twilio_phone_number":"+15005550006",
 * "twilio_user_password":"1ad19bda8aff34050549c306e2d7b961",
 * "users":[{"_id":"576d13fb09e6dd06af263e71",
 * "email":"myrna82@example.com",
 * "name":"Mrs. Leta McClure Sr.",
 * "organisation_id":"10001"}]}].
 *
 *
 */
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
        $org = $this->orgSet[1];

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
        $org = $this->orgSet[0];

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
        $org = $this->orgSet[1];

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
        $org = $this->orgSet[0];

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
        $org = $this->orgSet[1];

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
        $twilioPassword = bin2hex(random_bytes(16));
        $twilioUsername = bin2hex(random_bytes(16));
        $twilioName = 'twiliouser';
        $twilioEmail = preg_replace('/^[^@]*(.*)/', $twilioName . '$1', $user->email);
        $urlParts = [
            'scheme'=>config('app.external_scheme','http'),
            'host'=>config('app.external_host','external-host.com'),
            'port'=>config('app.external_port'),
            'user'=>$twilioUsername,
            'pass'=>$twilioPassword,
            'path'=>'/incoming/message'
        ];
        $twilio_inbound_message_request_url = http_build_url('',$urlParts);
        $urlParts['path']='/incoming/message/status';
        $twilio_outbound_message_status_callback_url = http_build_url('',$urlParts);

        $org = $this->orgSet[0];
        DB::collection('organisations')
            ->delete(['_id' => $org['_id']]);

        // Act

        $this->actingAs($user)->json('post', '/organisation', [
            'name' => $org['name'],
            'twilio_username' => $twilioUsername,
            'twilio_user_password' => $twilioPassword
        ]);

        // Assert
        $this->assertResponseOk();
        $data = json_decode($this->response->getContent(), true);
        $this->seeJsonStructure(
            [
                '_id',
                'name',
                'twilio_inbound_message_request_url',
                'twilio_outbound_message_status_callback_url',
                'auto_reply'
            ]);
        $this->seeJson([
            'name' => $org['name'],
            'twilio_inbound_message_request_url' => $twilio_inbound_message_request_url,
            'twilio_outbound_message_status_callback_url' => $twilio_outbound_message_status_callback_url,
            'auto_reply' => false,
        ]);

        $this->seeInDatabase('organisations',
            [
                ['name', $org['name']],
                ['first_user_id', $user->_id],
                ['twilio_user_id', '<>', null]
            ]);
        $this->seeInDatabase('users', [
            '_id' => $user->_id,
            'organisation_id' => $data['_id']]);
        $this->seeInDatabase('users', [
            'email' => $twilioEmail,
            'username'=>$twilioUsername,
            'organisation_id' => $data['_id']]);

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
        $org = $this->orgSet[0];

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
        $twilioPassword = bin2hex(random_bytes(16));
        $twilioUsername = bin2hex(random_bytes(16));

        // Act

        $noName = '';
        $this->actingAs($user)->json('post', '/organisation', [
            'name' => $noName,
            'twilio_username' => $twilioUsername,
            'twilio_user_password' => $twilioPassword
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
        $org = $this->orgSet[0];
        $twilioPassword = bin2hex(random_bytes(16));
        $twilioUsername = bin2hex(random_bytes(16));

        // Act

        $noName = '';
        $this->actingAs($user)->json('post', '/organisation', [
            'name' => $org['name'],
            'twilio_username' => $twilioUsername,
            'twilio_user_password' => $twilioPassword
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
        $twilioUsername = bin2hex(random_bytes(16));
        $twilioPassword = bin2hex(random_bytes(16));

        // Act

        $this->actingAs($user)->json('post', '/organisation', [
            'name' => $this->longname,
            'twilio_username' => $twilioUsername,
            'twilio_user_password' => $twilioPassword
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
        $org = $this->orgSet[0];

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
        $org = $this->orgSet[0];
        $twilioUsername = bin2hex(random_bytes(16));
        $twilioPassword = bin2hex(random_bytes(16));


        // Act

        $this->actingAs($user)->json('post', '/organisation', [
            'name' => 'Some Other Name',
            'twilio_username' => $twilioUsername,
            'twilio_user_password' => $twilioPassword
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
        $twilioUsername = bin2hex(random_bytes(16));
        $twilioPassword = bin2hex(random_bytes(16));

        // Act

        $this->actingAs($user)->json('post', '/organisation', [
            'name' => $this->longname,
            'twilio_username' => $twilioUsername,
            'twilio_user_password' => $twilioPassword
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
        $org = $this->orgSet[0];
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
        $org = $this->orgSet[0];
        $value = $value ?: $org[$propertyName];
        $storedValue = $storedValue ?: $value;
        DB::collection('organisations')
            ->delete(['_id' => $org['_id']]);
        $twilioPassword = bin2hex(random_bytes(16));
        $twilioUsername = bin2hex(random_bytes(16));
        
        // Act

        $this->actingAs($user)->json('post', '/organisation', [
            'name' => $org['name'],
            $propertyName => $value,
            'twilio_username' => $twilioUsername,
            'twilio_user_password' => $twilioPassword
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
        $org = $this->orgSet[0];
        DB::collection('organisations')
            ->delete(['_id' => $org['_id']]);
        $twilioPassword = bin2hex(random_bytes(16));
        $twilioUsername = bin2hex(random_bytes(16));

        // Act

        $this->actingAs($user)->json('post', '/organisation', [
            'name' => $org['name'],
            'twilio_username' => $twilioUsername,
            'twilio_user_password' => $twilioPassword,
            $propertyName => $badValue
        ]);

        // Assert
        $this->seeStatusCode(422);
        $this->seeJson([$propertyName => [$text]]);
    }

    private function notSeeJason($array)
    {
        try{
            $this->seeJson($array);
            $this->fail("NOT see items in Json");
        } catch (\Exception $e){

        }
    }

}
