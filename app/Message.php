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

    use OrganisationDateFormattingTrait;

    const STATUS_QUEUED     = 'queued';
    const STATUS_SENT       = 'sent';
    const STATUS_DELIVERED  = 'delivered';
    const STATUS_RECEIVED   = 'received';

    protected $fillable =
        [
            'message_text',
            'from',
            'queued_at',
            'sent_at',
            'delivered_at',
            'status',
            'organisation_id',
            'driver_id',
            'sid',
            'account_sid',
            'received_at'
        ];

    protected $hidden = [
        'organisation',
        'organisation_id',
        'driver_id',
        'created_at',
        'updated_at',
        'sid'
    ];
    protected $primaryKey = '_id';

    protected $dates = ['queued_at','sent_at','delivered_at','received_at'];

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
    public function getDeliveredAtAttribute($value){
        return $this->formatDate($value,$this->organisation);
    }
    public function getReceivedAtAttribute($value){
        return $this->formatDate($value,$this->organisation);
    }

}
