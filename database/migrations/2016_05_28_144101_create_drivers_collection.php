<?php

use Jenssegers\Mongodb\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDriversCollection extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('drivers', function (Blueprint $collection) {
//            $collection->increments('id');
//            $collection->string('organisation_id');
//            $collection->string('first_name');
//            $collection->string('last_name');
            $collection->index(['first_name','last_name']);
//            $collection->string('mobile_phone_number')->index();
            $collection->index('mobile_phone_number');
//            $collection->string('drivers_licence_number');
//            $collection->boolean('status');
//            $collection->timestamps();
//            $collection->foreign(['organisation_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('drivers');
    }
}
