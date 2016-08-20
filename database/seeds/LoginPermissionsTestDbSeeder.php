<?php

use Illuminate\Database\Seeder;

class LoginPermissionsTestDbSeeder extends Seeder
{
    use DbSeederTrait;

    protected function getFixture()
    {
        return [
            'users' => $this->fixtureUserSet,
            'password_resets' => [],
            'drivers' => $this->driverSet,
            'organisations' => $this->orgSet,
            'vehicles' => $this->vehicleSet,
            'messages' => $this->messageSet,
            'locations' => $this->locationSet
        ];
    }

}
