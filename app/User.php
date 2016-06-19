<?php

namespace TruckerTracker;

use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Moloquent;
use MongoDB\BSON\ObjectID;

/**
 * Class User
 * @package TruckerTracker
 *
 * @property ObjectID _id
 * @property boolean isLocked
 * @property string name
 * @property string email
 * @property Organisation organisation
 */

class User extends Moloquent implements
    AuthenticatableContract,
    AuthorizableContract,
    CanResetPasswordContract
{
    use Authenticatable, Authorizable, CanResetPassword;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'organisation_id'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token','created_at','updated_at'
    ];

    public function organisation(){
        return $this->belongsTo(Organisation::class,'organisation_id','_id');
    }

    public function firstUserOrganisation(){
        return $this->hasOne(Organisation::class,'first_user_id','_id');
    }
    public function twilioUserOrganisation(){
        return $this->hasOne(Organisation::class,'twilio_user_id','_id');
    }
}
