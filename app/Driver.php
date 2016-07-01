<?php

namespace TruckerTracker;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class Driver extends Eloquent
{
    protected $fillable = ['first_name', 'last_name','mobile_phone_number','drivers_licence_number','organisation_id'];
    protected $hidden = ['organisation_id'];

    public function organisation(){
        return $this->belongsTo(Organisation::class,'organisation_id','_id');
    }
    public function messages(){
        return $this->hasMany(Message::class,'driver_id','_id');
    }

}
