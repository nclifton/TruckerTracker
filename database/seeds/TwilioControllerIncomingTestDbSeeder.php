<?php

use Illuminate\Database\Seeder;

class TwilioControllerIncomingTestDbSeeder extends Seeder
{

    use DbSeederTrait;

    protected function getFixture()
    {
        return [
            'users' => [],
            'password_resets' => [],
            'organisations' => $this->orgSet,
            'drivers' => $this->driverSet,
            'vehicles' => $this->vehicleSet,
            'messages' => $this->messageSet,
            'locations' => $this->locationSet
        ];
    }
}
