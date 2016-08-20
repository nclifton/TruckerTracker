<?php

use Illuminate\Database\Seeder;

class MessageTestDbSeeder extends Seeder
{
    use DbSeederTrait;

    protected function getFixture()
    {
        return [
            'users' => $this->fixtureUserSet,
            'password_resets' => [],
            'organisations' => $this->orgSet,
            'drivers' => $this->driverSet,
            'vehicles' => $this->vehicleSet,
            'messages' => [],
            'locations' => []
        ];
    }

}
