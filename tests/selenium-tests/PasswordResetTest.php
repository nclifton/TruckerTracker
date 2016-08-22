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

        $this->assertEmailIsSent();

    }



}
