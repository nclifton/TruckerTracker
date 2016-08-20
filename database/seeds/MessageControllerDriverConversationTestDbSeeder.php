<?php

use Illuminate\Database\Seeder;

class MessageControllerDriverConversationTestDbSeeder extends Seeder
{

    use DbSeederTrait;

    /**
     * @return array
     */
    protected function getFixture()
    {
        return [
            'users' =>              [],
            'password_resets' =>    [],
            'organisations' =>      $this->orgSet,
            'drivers' =>            $this->driverSet,
            'vehicles' =>           [],
            'messages' =>           $this->conversationSet(),
            'locations' =>          []
        ];
    }
}
