<?php

include_once 'SeleniumTestLoader.php';

use PHPUnit_Extensions_Selenium2TestCase_Keys as Keys;
use \Zumba\PHPUnit\Extensions\Mongo\DataSet\DataSet;

class LoginTest extends SeleniumTestLoader
{

    
    protected function getFixture()
    {
        return [
            'users' => [],
            'password_resets' => [],
            'drivers' => [],
            'organisations' => [],
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
     * The Registration page is available.
     *
     * @test
     */
    public function testRegistrationAvailable()
    {
        // Arrange
        // Act

        $this->visit('/');
        $this->clickLink('http://localhost:8000/register');


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
        $this->seePageIs('/home');

        $this->assertCount(1, $this->getMongoConnection()
            ->collection('users')
            ->find(['name' =>  $user['username'],
                'email' => $user['emailAddress'],
                'organisation_id'=>['$exists'=>false]]));

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
        $this->assertThat($this->byId('organisationModalLabel')->text(), $this->equalTo('Organisation Editor'));
        $this->assertThat($this->byId('btn-save-organisation')->text(), $this->equalTo('Add Organisation'));

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
        
        // Act

        $this->registerUser($user);
        $this->closeOrganisationDialog();
        $this->logout();
        $this->byCssSelector('ul.navbar-right > li > a')->click();
        sleep(2);

        // Assert
        $this->seePageIs('/login');

        // Act some more
 
        $this->type($user['emailAddress'],'#email');
        $this->type($user['password'],'#password');
        $this->byCssSelector('button.btn.btn-primary[type="submit"]')->click();
        sleep(3);

        // Assert some more
        $this->assertThat($this->byId('organisationModalLabel')->text(), $this->equalTo('Organisation Editor'));

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
        $this->assertThat($this->byId('btn-add-organisation')->displayed(), $this->isTrue());
        $this->assertThat($this->byId('btn-edit-organisation')->displayed(), $this->isFalse());

        $this->assertThat($this->byId('btn-add-driver')->attribute('disabled'), $this->equalTo('true'));
        $this->assertThat($this->byId('btn-add-vehicle')->attribute('disabled'), $this->equalTo('true'));

    }

    protected function registerUser($user = null)
    {
        $user = empty($user) ? $this->loginUserSet[0] : $user;
        $this->visit('/');
        $this->byLinkText('Register')->click();
        sleep(1);
        $this->byName('name')->click();
        $this->keys($user['username']);
        $this->keys(Keys::TAB);
        $this->keys($user['emailAddress']);
        $this->keys(Keys::TAB);
        $this->keys($user['password']);
        $this->keys(Keys::TAB);
        $this->keys($user['password']);
        $this->keys(Keys::TAB);
        $this->keys(Keys::ENTER);
        sleep(2);  
        return $this;
    }

    protected function logout()
    {
        $this->byCssSelector('ul.navbar-right > li.dropdown a.dropdown-toggle')->click();
        sleep(2);
        $this->byCssSelector('ul.navbar-right > li.dropdown.open > ul.dropdown-menu > li > a')->click();
        sleep(2);
        return $this;
    }

    protected function closeOrganisationDialog()
    {
        $this->byCssSelector('#organisationModal > .modal-dialog > .modal-content > .modal-header > button.close')->click();
        sleep(2);
        return $this;
    }


}
