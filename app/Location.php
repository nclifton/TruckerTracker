<?php

namespace TruckerTracker;

//use Moloquent;
use Jenssegers\Mongodb\Eloquent\Model as Moloquent;
use MongoDB\BSON\ObjectID;
use MongoDB\BSON\UTCDatetime;

/**
 * Class Location
 * @package TruckerTracker
 * 
 * @property ObjectID vehicle_id
 * @property UTCDatetime send_at
 * @property UTCDatetime queued_at
 * @property string status
 * @property ObjectID organisation_id
 * @property string sid
 * @property string sid_response
 * @property float latitude
 * @property float longitude
 * @property float course
 * @property float speed
 * @property UTCDatetime datetime
 */
class Location extends Moloquent
{

    use OrganisationDateFormatingTrait;

    protected $fillable = 
        [
            'vehicle_id',
            'sent_at',
            'queued_at',
            'status',
            'organisation_id',
            'sid',
            'sid_response',
            'latitude',
            'longitude',
            'course',
            'speed',
            'datetime'
        ];
    
    protected $dates = ['sent_at','queued_at', 'datetime'];

    public function vehicle(){
        return $this->belongsTo(Vehicle::class,'vehicle_id','_id');
    }
    public function organisation(){
        return $this->belongsTo(Organisation::class,'organisation_id','_id');
    }
    public function getSentAtAttribute($value){
        return $this->formatDate($value,$this->organisation);
    }
    public function getQueuedAtAttribute($value){
        return $this->formatDate($value,$this->organisation);
    }
}
