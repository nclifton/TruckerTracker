<?php

namespace TruckerTracker;

use Artisan;
use DB;

require_once __DIR__ . '/IntegratedTestCase.php';

class LoginTest extends IntegratedTestCase
{

    protected function artisanSeedDb()
    {
        Artisan::call('db:seed', ['--class' => 'LoginTestDbSeeder']);
    }

    /*
     * @before
     */
    public function setUp()
    {
        parent::setUp();
    }

    /**
     * The Registration page is available.
     *
     * @test
     */
    public function testRegistrationAvailable()
    {
        // Arrange
        // Act

        $this->visit('/');
        $this->click('Register');


        // Assert
        $this->seePageIs('/register');


    }

    /**
     * A can register.
     *
     * @return void
     */
    public function testRegistrationWorks()
    {
        // Arrange
        $user = $this->loginUserSet[0];

        // Act

        $this->registerUser($user);

        // Assert

        $this->assertCount(1, DB::collection('users')
            ->where(['name' => $user['name'],
                'email' => $user['email'],
                'organisation_id' => ['$exists' => false]])->get());

    }

    /**
     * forces to add organisation on first login
     *
     * @return void
     */
    public function testAskedToAddOrganisation()
    {
        // Arrange
        $user = $this->loginUserSet[0];

        // Act
        $this->registerUser($user);

        // Assert
        $this->see('Organisation Editor');
        $this->assertThat($this->byId('orgModalLabel')->text(), $this->equalTo('Organisation Editor'));
        $this->assertThat($this->byId('btn-save-org')->text(), $this->equalTo('Add Organisation'));

    }

    /**
     * A can register.
     *
     * @return void
     */
    public function testLogoutLogin()
    {
        // Arrange
        $user = $this->loginUserSet[0];

        // Act & Assert

        $this->registerUser($user)->closeOrganisationDialog()->logout()->click('Login');

        //Assert
        $this->assertTrue($this->byCssSelector('.panel-heading')->displayed());
        $this->assertEquals('Login',$this->byCssSelector('.panel-heading')->text());
        $this->type($user['email'], '#email');
        $this->type($user['password'], '#password');
        $this->byCssSelector('button.btn.btn-primary[type="submit"]')->click();
        $this->wait(4000);
        $this->assertThat($this->byId('orgModalLabel')->text(), $this->equalTo('Organisation Editor'));

    }

    /**
     * add driver and add vehicle buttons disabled if there's no organisation, also the edit organisation button
     * is replaced by an add organisation button
     *
     * @return void
     */
    public function testNoOrgButtonArrangement()
    {
        // Arrange
        $user = $this->loginUserSet[0];

        // Act
        $this->registerUser($user)->closeOrganisationDialog();

        // Assert
        $this->assertThat($this->byId('btn-add-org')->displayed(), $this->isTrue());
        $this->assertThat($this->byId('btn-add-org')->text(), $this->equalTo('Add Organisation'));

        $this->assertThat($this
            ->byId('btn-add-driver')
            ->displayed(),$this
            ->isFalse());

        $this->assertThat($this
            ->byId('btn-add-vehicle')
            ->displayed(), $this
            ->isFalse());


    }

    protected function registerUser($user = null)
    {
        $user = empty($user) ? $this->loginUserSet[0] : $user;
        $this->visit('/')
            ->click('Register')
            ->type($user['name'], 'name')
            ->type($user['email'], 'email')
            ->type($user['password'], 'password')
            ->type($user['password'], 'password_confirmation')
            ->findByCssSelector('button[type="submit"]')
            ->click();
        sleep(2);
        return $this;

    }

    protected function logout()
    {
        $this->byCssSelector('ul.navbar-right > li.dropdown a.dropdown-toggle')->click();
        //sleep(2);
        $this->byCssSelector('ul.navbar-right > li.dropdown.open > ul.dropdown-menu > li > a')->click();
        //sleep(3);
        return $this;
    }

    protected function closeOrganisationDialog()
    {
        $this->byCssSelector('#orgModal > .modal-dialog > .modal-content > .modal-header > button.close')->click();
        sleep(2);
        return $this;
    }


}
