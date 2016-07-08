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
 * @property Organisation organisation
 */
class Location extends Moloquent
{

    use OrganisationDateFormattingTrait;

    protected $fillable = 
        [
            'vehicle_id',
            'queued_at',
            'sent_at',
            'delivered_at',
            'received_at',
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
    
    protected $dates = ['queued_at','sent_at','delivered_at','received_at', 'datetime'];

    protected $hidden = [
        'organisation_id',
        'vehicle_id',
        'organisation',
        'created_at',
        'updated_at',
        'sid'
    ];

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
    public function getDeliveredAtAttribute($value){
        return $this->formatDate($value,$this->organisation);
    }
    public function getReceivedAtAttribute($value){
        return $this->formatDate($value,$this->organisation);
    }
    public function getDatetimeAttribute($value){
        return $this->formatDate($value,$this->organisation);
    }
}
