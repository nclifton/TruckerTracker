<?php

namespace TruckerTracker\Events;

use TruckerTracker\Events\Event;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

use TruckerTracker\Location;

class LocationUpdate extends Event implements ShouldBroadcast
{
    use SerializesModels;

    public $location;

    /**
     * Create a new event instance.
     *
     * @param Location $location
     */
    public function __construct(Location $location)
    {
        $this->location = $location;
    }

    /**
     * Get the channels the event should be broadcast on.
     *
     * @return array
     */
    public function broadcastOn()
    {
        return ['trucker_tracker.'.$this->location->organisation->_id];
    }
}
