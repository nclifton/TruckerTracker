<?php

use Illuminate\Database\Seeder;

class TwilioControllerLocationTestDbSeeder extends Seeder
{

    use DbSeederTrait;
    protected function getFixture()
    {

        //$this->orgset[0] = array_merge($this->orgset[0],$this->twilio_cwf);

        return [
            'users' => [],
            'password_resets' => [],
            'organisations' => $this->orgSet,
            'drivers' => $this->driverSet,
            'vehicles' => $this->vehicleSet,
            'messages' => [],
            'locations' => $this->locationSet
        ];
    }
}
