<?php

namespace TruckerTracker;


include_once 'IntegratedTestCase.php';

class ConfigOrgTest extends IntegratedTestCase
{


    protected $orgset = [
        'test' => [
            'name' => 'McSweeney Transport Group',
            'timezone' => 'Australia/Sydney',
            'datetime_format' => 'H:i:s d/m/y',
            'twilio_account_sid' => 'AC392e8d8bc564eb45ea67cc0f3a8ebf3c',
            'twilio_auth_token' => '36c8ee5499df1e116aa53b1ee05ca5fa',
            'twilio_phone_number' => '+61419140683'

        ],
        'blank' => [
            'name' => ''
        ],
        'bad' => [
            'name' => 'a very long organisation name has to be more than 128 character long like this one when I add a lot fo words to the organisation name',
            'twilio_account_sid' => 'XX392e8d8bc564eb45ea67cc0f3a8ebf3c',
            'twilio_auth_token' => '36',
            'twilio_phone_number' => '614191683'
        ],
        'other' => [
            'name' => 'Some Other Organisation',
            'timezone' => 'Australia/Perth',
            'datetime_format' => 'h:i:s A D d/m/y',
            'twilio_account_sid' => 'AC402e8d8bc564eb45ea67cc0f3a8ebf3c',
            'twilio_auth_token' => '37c8ee5499df1e116aa53b1ee05ca5fa',
            'twilio_phone_number' => '+61419140684'
        ]

    ];
    protected $fixtureUserset = [
        [
            '_id' => '100',
            'name' => 'firstUser',
            'email' => 'test1@cliftonwebfoundry.com.au',
            'password' => '$2y$10$NkvhsSZvHX57Bm993h0ddeXdCrHwQ/X4idWV.pojZU9j3hDMmx2RG'
        ],

    ];
    protected $newUserLogin = [
        'name' => 'op1User',
        'email' => 'op1User@mcsweeneytg.com.au',
        'password' => 'mstgpwd1'
    ];

    protected function getFixture()
    {
        $userset = $this->fixtureUserset;
        foreach ($userset as $key => $user) {
            unset($userset[$key]['organisation_id']);
        }
        return [
            'users' => $this->fixtureUserset,
            'password_resets' => [],
            'drivers' => [],
            'organisations' => [],
            'vehicles' => [],
            'messages' => []
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
     * Add Org dialog displayed on login when not defined
     * ensure Users Tab panel is inaccessible
     * close and reopen the organisation dialog
     *
     * @return void
     *
     * @test
     */
    public function testReopenOrgDialog()
    {

        // Act
        $this->login()->closeOrganisationDialog();
        $this->byId('btn-add-org')->click();
        sleep(1);


        // Assert
        $this->assertThat($this->byId('orgModalLabel')->text(), $this->equalTo('Organisation Editor'));
        $this->assertThat($this->byId('btn-save-org')->text(), $this->equalTo('Add Organisation'));
        $this->byId('org-users-tab-link')->click();
        $this->wait(3);
        $this->see('Organisation Name');

    }

    /**
     * can add organisation with
     * - name
     * - timezone
     * - datetime_format
     * 
     * - twilio_account_sid
     * - twilio_auth_token
     * - twilio_phone_number
     *
     * - twilio_inbound_message_request_url
     * - twilio_outbound_message_status_callback_url
     *
     * @return void
     *
     * @test
     */
    public function testAddsOrg()
    {
        // Arrange
        $org = $this->orgset['test'];


        // Act
        $this
            ->login();

        // the org dialog is expected to be automatically present
        $this
            ->clearType($org['name'], '#org_name');
        $this
            ->select('timezone', $org['timezone'])
            ->wait(500)
            ->select('datetime_format', $org['datetime_format'])
            ->wait(500);

        // Twilio mobile network gateway properties are on a separate tab
        $this
            ->byId('org-twilio-tab-link')->click();
        $this
            ->wait(1000)
            ->clearType($org['twilio_account_sid'], '#twilio_account_sid')
            ->clearType($org['twilio_auth_token'], '#twilio_auth_token')
            ->clearType($org['twilio_phone_number'], '#twilio_phone_number');

        // Assert

        // we have the twilio username and password as hidden input
        $this
            ->assertThat($this
                ->byCssSelector('input#twilio_username')
                ->attribute('type'), $this
                ->equalTo('hidden'));
        $this
            ->assertThat($this
                ->byCssSelector('input#twilio_user_password')
                ->attribute('type'), $this
                ->equalTo('hidden'));

        // Arrange
        $twilioUsername = $this
            ->byId('twilio_username')
            ->attribute('value');
        $twilioPassword = $this
            ->byId('twilio_user_password')
            ->attribute('value');

        // the twilio urls to use are displayed
        $urlParts = [
            'scheme' => config('app.external_scheme', 'http'),
            'user' => $twilioUsername,
            'pass' => $twilioPassword,
            'host' => config('app.external_host', 'example.com'),
            'port' => config('app.external_port'),
            'path' => '/incoming/message'
        ];
        $twilio_inbound_message_request_url = http_build_url('', $urlParts);
        $urlParts['path'] = '/incoming/message/status';
        $twilio_outbound_message_status_callback_url = http_build_url('', $urlParts);

        $this
            ->assertThat(
                $this
                    ->byId('twilio_inbound_message_request_url')
                    ->attribute('value'),
                $this
                    ->equalTo($twilio_inbound_message_request_url));
        $this
            ->assertThat(
                $this
                    ->byId('twilio_outbound_message_status_callback_url')
                    ->attribute('value'),
                $this
                    ->equalTo($twilio_outbound_message_status_callback_url));


        // Act - save

        $this
            ->byId('btn-save-org')
            ->click();
        $this->wait(1000);
        // Assert
        $attempts = 0;
        while ($attempts < 10) {
            try {
                $this
                    ->assertThat($this
                        ->byId('btn-add-org')
                        ->displayed(), $this
                        ->isFalse());
                break;
            } catch (\Exception $e) {

            }
            $this->wait();
            ++$attempts;
        }

        $this
            ->assertThat($this
                ->byId('btn-edit-org')
                ->displayed(), $this
                ->isTrue());
        $this
            ->assertThat($this
                ->byId('btn-add-driver')
                ->attribute('disabled'), $this
                ->isNull());
        $this
            ->assertThat($this
                ->byId('btn-add-vehicle')
                ->attribute('disabled'), $this
                ->isNull());

        $this->assertCount(1, $this
            ->getMongoConnection()
            ->collection('users')
            ->find([
                'name' => $this->fixtureUserset[0]['name'],
                'organisation_id' => ['$exists' => true]
            ]));

        $this
            ->seeInDatabase('organisations', $this->orgset['test']);


    }

    /**
     * can add organisation
     *
     * @return void
     *
     * @test
     */
    public function testAddEmptyOrgValidationFail()
    {
        // Arrange

        // Act
        $this->login()->addOrg('blank');

        // Assert #orgForm > div:nth-child(4)
        $this->assertThat($this->byId('orgModalLabel')->text(), $this->equalTo('Organisation Editor'));
        $this->assertThat(explode(' ', $this->byCssSelector('#orgConfigForm > div:nth-child(1)')->attribute('class')),
            $this->contains('has-error'));
        $this->assertThat($this->byCssSelector('#orgConfigForm > div:nth-child(1) > div > span > strong')->text(),
            $this->equalTo('We do need a name for your organisation'));


    }

    /**
     * duplicate organisation allowed
     *
     * @return void
     *
     * @test
     */
    public function testAddDuplicateorgValidationPass()
    {
        // Arrange
        $org = $this->orgset['test'];
        $this->dbAddOrg('test');

        // Act
        $this->login()->addOrg('test');

        // Assert
        $this->assertCount(2, $this->getMongoConnection()
            ->collection('organisations')
            ->find(['name' => $org['name']]), $org['name'] . ' is in organisation');
        $this->assertCount(1, $this->getMongoConnection()
            ->collection('users')
            ->find(['name' => $this->fixtureUserset[0]['name'], 'organisation_id' => ['$exists' => true]]), 'user count');


    }

    /**
     * validation all fields
     *
     * @return void
     *
     * @test
     */
    public function validatesFail()
    {

        // Act
        $this->login()->addOrg('bad');

        // Assert
        $this->assertThat($this->byId('orgModalLabel')->text(), $this->equalTo('Organisation Editor'));

        $this->byId('org-config-tab-link')->click();

        $this->assertThat(explode(' ', $this
            ->byCssSelector('#orgConfigForm > div:nth-child(1)')
            ->attribute('class')),
            $this->contains('has-error'));
        $this->assertThat($this
            ->byCssSelector('#orgConfigForm > div:nth-child(1) > div > span > strong')
            ->text(),
            $this->equalTo('That name for you organisation is too long, make it less than 128'));

        $this->byId('org-twilio-tab-link')->click();
        
        $this->assertThat(explode(' ', $this
            ->byCssSelector('#orgTwilioForm > div:nth-child(1)')
            ->attribute('class')),
            $this->contains('has-error'));
        $this->assertThat($this
            ->byCssSelector('#orgTwilioForm > div:nth-child(1) > div > span > strong')
            ->text(),
            $this->equalTo('That does not match the pattern of a Twilio Account SID, please check'));

        $this->assertThat(explode(' ', $this
            ->byCssSelector('#orgTwilioForm > div:nth-child(2)')
            ->attribute('class')),
            $this->contains('has-error'));
        $this->assertThat($this
            ->byCssSelector('#orgTwilioForm > div:nth-child(2) > div > span > strong')
            ->text(),
            $this->equalTo('That does not match the pattern of a Twilio Authentication Token, please check'));

        $this->assertThat(explode(' ', $this
            ->byCssSelector('#orgTwilioForm > div:nth-child(3)')
            ->attribute('class')),
            $this->contains('has-error'));
        $this->assertThat($this
            ->byCssSelector('#orgTwilioForm > div:nth-child(3) > div > span > strong')
            ->text(),
            $this->equalTo('That doesn\'t look like an australian phone number, it needs to have 10 digits and start with a 0 or start with +61 and have 11 digits'));

    }

    /**
     * edit added org dialog
     *
     * @return void
     *
     * @test
     */
    public function testEditOrg()
    {

        // Act
        $this->login()->addOrg();
        $this->byId('btn-edit-org')->click();
        $this->wait();

        // Assert
        $this->assertThat($this->byId('orgModalLabel')->text(), $this->equalTo('Organisation Editor'));
        $this->assertThat($this->byId('btn-save-org')->text(), $this->equalTo('Save Changes'));

        // Act some more
        $this->addOrg('other');

        // Assert some more
        $this->assertThat($this->byId('heading_org_name')->text(),
            $this->equalTo($this->orgset['other']['name']));

        $this->seeInDatabase('organisations', $this->orgset['other']);

    }


    /**
     * Diver Dialog displayed to add vehicle
     *
     * @return void
     *
     * @test
     */
    public function testAddVehicleDialogDisplayed()
    {
        // Arrange

        // Act
        $this->login()->addOrg();
        $this->clickOnElement('btn-add-vehicle');
        sleep(2); // wait for animation

        // Assert
        $this->assertThat($this->byId('vehicleModalLabel')->displayed(), $this->isTrue());

    }

    /**
     * Can Add a Vehicle
     *
     * @return void
     *
     * @test
     */
    public function addsVehicle()
    {
        // Arrange

        // Act
        $this->login()->addOrg();
        $this->addVehicle();

        // Assert
        $vehicle = $this->vehicleSet[0];
        $this->assertCount(1, $this->getMongoConnection()
            ->collection('vehicles')
            ->find([
                'registration_number' => $vehicle['registration_number'],
                'mobile_phone_number' => $vehicle['mobile_phone_number'],
                'tracker_imei_number' => $vehicle['tracker_imei_number'],
                'organisation_id' => ['$exists' => true]
            ]));

        $this->assertThat($this->byId('vehicleModalLabel')->displayed(), $this->isFalse());

    }

    /**
     * Can Add a User to saved org
     *
     * @return void
     *
     * @test
     */
    public function addsUser()
    {
        // Arrange

        // Act
        $this->login()->addOrg()->addOrgUser();
        $this->seeById('btn-edit-org');

        // Assert

        $this->seeInDatabase('users', $this->userWhere($this->newUserLogin));


    }

    /**
     * Can Edit/change a User name and email - not changing password
     *
     * @return void
     *
     * @test
     */
    public function editsUser()
    {
        // Arrange
        $this->login()->addOrg()->addOrgUser();

        // Act
        $this->byId('btn-edit-org')->click();
        $this->wait();
        $this->byCssSelector('#user_list li:nth-child(1) > span > button.open-modal-user')->click();
        $this->wait();
        $this->see('Edit Organisation User');
        $this->see($this->newUserLogin['name']);
        $this->see($this->newUserLogin['email']);
        $this->byId('btn-save-user')->click();
        $this->wait();
        $this->byId('btn-save-org')->click();
        $this->waitForElement('btn-edit-org');


    }

    /**
     * Can delete a User
     *
     * @return void
     *
     * @test
     */
    public function deletesUser()
    {

        // Arrange
        $this->login()->addOrg()->addOrgUser();

        // Act
        $this->byId('btn-edit-org')->click();
        $this->wait();
        $this->byCssSelector('#user_list li:nth-child(1) > span > button.delete-user')->click();
        $this->wait();
        try{
            $this->byCssSelector('#user_list li:nth-child(1) > span > button.delete-user');
        } catch (\RuntimeException $e){
            //
        }
        $this->byId('btn-save-org')->click();
        $this->waitForElement('btn-edit-org');
    }

    /**
     * Can validates and saves organisation when click add a user
     *
     * @return void
     *
     * @skip
     */
    public function validateOrganisationWhenOpeningTheUserDialog()
    {
        // Arrange
        $this->login();

        // Act

        $org = $this->orgset['blank'];

        $this->clearType($org['name'], '#org_name');

        $this->byId('org-users-tab-link')->click();
        $this->wait();
        $this->byId('btn-add-user')->click();
        $this->wait();

        $this->assertThat($this->byCssSelector('#orgConfigForm > div:nth-child(1)')->displayed(),$this->isTrue());
        $this->assertThat(explode(' ', $this->byCssSelector('#orgForm > div:nth-child(1)')->attribute('class')),
            $this->contains('has-error'));
        $this->assertThat($this->byCssSelector('#orgConfigForm > div:nth-child(1) > div > span > strong')->text(),
            $this->equalTo('We do need a name for your organisation'));

    }


    /**
     * @param string $orgkey
     *
     * - name
     * - twilio_account_sid
     * - twilio_auth_token
     * - twilio_phone_number
     * - timezone
     * - datetime_format
     *
     * @return $this
     */
    protected function addOrg($orgkey = 'test')
    {
        $org = $this->orgset[$orgkey];

        $this->clearType($org['name'], '#org_name');
        if (isset($org['timezone']))
            $this->select('timezone', $org['timezone']);
        if (isset($org['datetime_format']))
            $this->select('datetime_format', $org['datetime_format']);
        $this->byId('org-twilio-tab-link')->click();
        if (isset($org['twilio_account_sid']))
            $this->clearType($org['twilio_account_sid'], '#twilio_account_sid');
        if (isset($org['twilio_auth_token']))
            $this->clearType($org['twilio_auth_token'], '#twilio_auth_token');
        if (isset($org['twilio_phone_number']))
            $this->clearType($org['twilio_phone_number'], '#twilio_phone_number');
        $this->byId('org-config-tab-link')->click();

        $this->byId('btn-save-org')->click();
        $this->wait(3000); // wait for animation
        return $this;
    }


    protected function addVehicle($vKey = 0)
    {
        $vehicle = $this->vehicleSet[$vKey];

        $this->clickOnElement('btn-add-vehicle');
        sleep(2); // wait for animation

        $this->type($vehicle['registration_number'], '#registration_number');
        $this->type($vehicle['mobile_phone_number'], '#vehicle_mobile_phone_number');
        $this->type($vehicle['tracker_imei_number'], '#tracker_imei_number');
        $this->clickOnElement('btn-save-vehicle');
        sleep(2); // wait for animation

        return $this;
    }

    private function dbAddOrg($orgkey = 'test')
    {

        $this->getMongoConnection()->collection('organisations')->insert($this->orgset[$orgkey]);
        return $this;
    }

    protected function closeOrganisationDialog()
    {
        $this->byCssSelector('#orgModal > .modal-dialog > .modal-content > .modal-header > button.close')->click();
        sleep(2);
        return $this;
    }

    private function userWhere($user)
    {
        $where = $user;
        unset($where['password']);
        return $where;
    }

    protected function addOrgUser()
    {

        $this->waitForElement('btn-edit-org');
        $this->byId('btn-edit-org')->click();
        $this->wait();
        $this->byId('org-users-tab-link')->click();
        $this->wait();
        $this->byId('btn-add-user')->click();
        $this->wait(6000);
        $this->type($this->newUserLogin['name'], 'user_name');
        $this->type($this->newUserLogin['email'], 'email');
        $this->type($this->newUserLogin['password'], 'password');
        $this->type($this->newUserLogin['password'], 'password_confirmation');
        $this->byId('btn-save-user')->click();
        $this->wait();
        $this->byId('btn-save-org')->click();
        $this->wait();
    }


}
