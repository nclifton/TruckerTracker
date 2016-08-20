<?php

use Illuminate\Database\Seeder;

class LoginTestDbSeeder extends Seeder
{

    use DbSeederTrait;

    protected function getFixture()
    {
        return [
            'users' => [],
            'password_resets' => [],
            'drivers' => [],
            'organisations' => [],
            'vehicles' => []
        ];
    }

}
