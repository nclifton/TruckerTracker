<?php

namespace TruckerTracker;

use Artisan;
use DB;

require_once __DIR__ . '/IntegratedTestCase.php';
require_once __DIR__ . '/../EmailTestTrait.php';


class PasswordResetTest extends IntegratedTestCase
{

    use EmailTestTrait;

    protected function artisanSeedDb()
    {
        Artisan::call('db:seed', ['--class' => 'PasswordResetTestDbSeeder']);
    }


    /**
     * The Registration page is available.
     *
     * @test
     */
    public function testForgottenPassword()
    {
        // Arrange
        $user = $this->fixtureUserSet[0];

        // Act

        $this->visit('/login');
        $this->assertThat($this->byCssSelector('.btn.btn-link')->text(), $this->equalTo('Forgot Your Password?'));
        $this->byCssSelector('.btn.btn-link')->click();
        $this->assertThat($this->byCssSelector('.panel-heading')->text(), $this->equalTo('Reset Password'));
        $this->clearType($user['email'],'email');
        $this->byCssSelector('button.btn.btn-primary')->click();
        $this->assertThat($this->byCssSelector('.alert.alert-success')->text(),$this->equalTo('We have e-mailed your password reset link!'));
        $this->closeBrowser();

        $this->assertEmailIsSent();
        $email = $this->getLastMessage();
        $this->assertEmailSubjectContains('Your Password Reset Link',$email);
        $this->assertEmailHtmlContains('href="http://local.truckertracker.services/password/reset',$email);

        $links = $this->getLinksInEmailHtml($email);
        $this->assertThat(array_column($links,'text'),$this->contains('Reset your password'));
        $this->visit($links[array_search('Reset your password', array_column($links,'text'))]['href']);

        $this->assertThat($this->byCssSelector('.panel-heading')->text(), $this->equalTo('Reset Password'));

        $this->assertTrue($this->byId('password')->displayed());
        $this->assertTrue($this->byId('password-confirm')->displayed());
        $this->assertThat($this->byCssSelector('button.btn.btn-primary')->text(),$this->equalTo('Reset Password'));


    }


}
