<?php

namespace TruckerTracker;

//use Moloquent;
use Jenssegers\Mongodb\Eloquent\Model as Moloquent;


/**
 * @property-read \TruckerTracker\Driver $driver
 * @property-read \TruckerTracker\Organisation $organisation
 * @property mixed $sent_at
 * @property mixed $queued_at
 * @property-read mixed $_id
 * @property string status
 * @property string sid
 * @property string account_sid
 */
class Message extends Moloquent
{

    use OrganisationDateFormatingTrait;

    protected $fillable =
        [
            'message_text',
            'from',
            'queued_at',
            'sent_at',
            'status',
            'organisation_id',
            'driver_id',
            'sid',
            'account_sid',
            'received_at'
        ];

    protected $hidden = ['organisation'];

    protected $dates = ['sent_at','queued_at', 'received_at'];

    public function driver(){
        return $this->belongsTo(Driver::class,'driver_id','_id');
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
