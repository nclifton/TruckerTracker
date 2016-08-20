<?php

use Illuminate\Database\Seeder;

class ConfigDriversTestDbSeeder extends Seeder
{

    use DbSeederTrait;

    protected function getFixture()
    {
        return [
            'users' => $this->fixtureUserSet,
            'password_resets' => [],
            'organisations' => $this->orgSet,
            'drivers' => [],
            'vehicles' => [],
            'messages' => [],
            'locations' => []
        ];
    }
}
