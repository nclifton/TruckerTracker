<?php

use Illuminate\Database\Seeder;

class ConfigOrgTestDbSeeder extends Seeder
{
    use DbSeederTrait;

    protected function getFixture()
    {
        return [
            'users' => $this->fixtureUserSetNoOrg,
            'password_resets' => [],
            'drivers' => [],
            'organisations' => [],
            'vehicles' => [],
            'messages' => []
        ];
    }
}
