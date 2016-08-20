<?php

use Illuminate\Database\Seeder;

class TwilioControllerTestCaseDbSeeder extends Seeder
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
            'messages' => [],
            'locations' => []
        ];
    }
}
