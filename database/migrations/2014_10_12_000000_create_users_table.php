<?php

use Jenssegers\Mongodb\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{

    /**
     * The name of the database connection to use.
     *
     * @var string
     */
    protected $connection = 'mongodb';
    
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $collection) {
            //$collection->increments('id');
            //$collection->string('name');
            //$collection->string('email')->unique();
            $collection->unique('email');
            //$collection->string('password');
            //$collection->string('organisation_id');
            //$collection->rememberToken();
            //$collection->timestamps();
            //$collection->foreign(['organisation_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('users');
    }
}
