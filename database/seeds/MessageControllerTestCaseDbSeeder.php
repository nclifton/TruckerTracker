<?php

use Illuminate\Database\Seeder;

class MessageControllerTestCaseDbSeeder extends Seeder
{

    use DbSeederTrait;

    protected function getFixture()
    {

        return [
            'users' => [],
            'password_resets' => [],
            'organisations' => $this->orgSet,
            'drivers' => $this->driverSet,
            'vehicles' => [],
            'messages' => $this->messageSet,
            'locations' => []
        ];
    }



}
