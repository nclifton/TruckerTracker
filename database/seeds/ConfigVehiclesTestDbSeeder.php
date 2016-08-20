<?php

use Illuminate\Database\Seeder;

class ConfigVehiclesTestDbSeeder extends Seeder
{

    use DbSeederTrait;
    /**
     * @var array
     * @return array
     */
    protected function getFixture()
    {
        return [
            'users' => $this->fixtureUserSet,
            'password_resets' => [],
            'organisations' => $this->orgSet,
            'drivers' => [],
            'vehicles' => []
        ];
    }
}
