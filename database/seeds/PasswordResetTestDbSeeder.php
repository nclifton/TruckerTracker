<?php

use Illuminate\Database\Seeder;

class PasswordResetTestDbSeeder extends Seeder
{
    use DbSeederTrait;

    protected function getFixture()
    {
        return [
            'users' => $this->fixtureUserSet,
            'password_resets' => [],
            'drivers' => [],
            'organisations' => [],
            'vehicles' => [],
            'messages' => [],
            'locations' => []
        ];
    }
}
