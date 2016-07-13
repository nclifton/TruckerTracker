<?php

namespace TruckerTracker;

use Moloquent;
use MongoDB\BSON\ObjectID;

/**
 * @property string name',
 * @property string twilio_account_sid
 * @property string twilio_auth_token
 * @property string twilio_phone_number
 * @property string twilio_user_password'
 * @property string timezone
 * @property string datetime_format
 * @property boolean auto_reply
 * @property ObjectID first_user_id
 * @property User firstUser
 * @property ObjectID twilio_user_id
 * @property User twilioUser
 * @property array users
 * @property array drivers
 * @property array vehicles
 * @property ObjectID _id
 */
class Organisation extends Moloquent
{
    protected $fillable =
        [
            'name',
            'twilio_account_sid',
            'twilio_auth_token',
            'twilio_phone_number',
            'timezone',
            'hour12',
            'first_user_id',
            'twilio_user_id',
            'twilio_user_password',
            'auto_reply'
        ];

    protected $hidden = [
        'twilio_user_id',
        'first_user_id',
        'created_at',
        'updated_at',
        'first_user',
        'twilio_user'
    ];

    public function firstUser()
    {
        return $this->belongsTo(User::class,'first_user_id','_id');
    }

    public function twilioUser()
    {
        return $this->belongsto(User::class,'twilio_user_id','_id');
    }

    public function users()
    {
        return $this->hasMany(User::class,'organisation_id','_id');
    }

    public function drivers()
    {
        return $this->hasMany(Driver::class,'organisation_id','_id');
    }

    public function vehicles()
    {
        return $this->hasMany(Vehicle::class,'organisation_id','_id');
    }
    public function messages()
    {
        return $this->hasMany(Message::class,'organisation_id','_id');
    }
    public function locations()
    {
        return $this->hasMany(Location::class,'organisation_id','_id');
    }

    public function scopeAddedUsers()
    {
        return $this->users()->whereNotIn('_id',[$this->first_user_id,$this->twilio_user_id]);
    }
    
}
