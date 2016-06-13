<?php

use Jenssegers\Mongodb\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVehiclesCollection extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vehicles', function (Blueprint $collection) {
            //$collection->increments('id');
//            $collection->string('organisation_id');
//            $collection->string('registration_number')->index();
            $collection->index('registration_number');
//            $collection->string('mobile_phone_number')->index();
            $collection->index('mobile_phone_number');
//            $collection->string('tracker_imei_number');
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
        Schema::drop('vehicles');
    }
}
