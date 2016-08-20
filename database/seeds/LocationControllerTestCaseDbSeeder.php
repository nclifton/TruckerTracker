<?php

use Illuminate\Database\Seeder;

class LocationControllerTestCaseDbSeeder extends Seeder
{
    use DbSeederTrait;

    /**
     * @return array
     */
    protected function getFixture()
    {
        return [
            'users' => [],
            'password_resets' => [],
            'organisations' => $this->orgSet,
            'drivers' => [],
            'vehicles' => $this->vehicleSet,
            'messages' => [],
            'locations' => json_decode($this->viewLocationSetJson,true)
        ];
    }
}
