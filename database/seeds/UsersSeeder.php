<?php

use Illuminate\Database\Seeder;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        
        Schema::drop('organisations');
        Schema::drop('password_resets');
        Schema::drop('users');

    }
}
