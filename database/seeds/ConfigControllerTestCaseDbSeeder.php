<?php

use Illuminate\Database\Seeder;

class ConfigControllerTestCaseDbSeeder extends Seeder
{
    use DbSeederTrait;

    protected function getFixture()
    {
        return [
            'users' => [],
            'password_resets' => [],
            'organisations' => $this->orgSet,
            'drivers' => [],
            'vehicles' => []
        ];
    }
}