<?php

use Illuminate\Database\Seeder;

class LocationTestDbSeeder extends Seeder
{

    use DbSeederTrait;

    protected function getFixture()
    {
        return [
            'users' => $this->fixtureUserSet,
            'password_resets' => [],
            'organisations' => $this->orgSet,
            'drivers' => [],
            'vehicles' => $this->vehicleSet,
            'messages' => [],
            'locations' => []
        ];
    }

}
