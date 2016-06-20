<?php

namespace TruckerTracker;

use Moloquent;

/**
 * @property mixed organisation
 * @property mixed mobile_phone_number
 */
class Vehicle extends Moloquent
{
    protected $fillable =
        [
            'registration_number',
            'mobile_phone_number',
            'tracker_imei_number',
            'tracker_password',
            'organisation_id'
        ];

    protected $hidden =
        [
            'organisation_id',
            'created_at',
            'changed_at',
            'tracker_password'
    ];

    public function organisation(){
        return $this->belongsTo(Organisation::class,'organisation_id','_id');
    }
    public function locations(){
        return $this->hasMany(Location::class,'vehicle_id','_id');
    }
}
