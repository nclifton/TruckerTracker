<?php

namespace TruckerTracker\Http\Controllers;

use Auth;
use Gate;
use SSE;

use Illuminate\Support\Facades\Redis;
use Log;
use Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use TruckerTracker\Http\Requests;
use TruckerTracker\Location;

class LocationController extends Controller
{

    /**
     * Create a new controller instance.
     *
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * @param Location $loc
     * @return \Illuminate\Http\JsonResponse
     */
    public function getLocation(Location $loc)
    {
        if (Gate::denies('view-location', $loc->organisation)) {
            abort(403);
        }
        return Response::json($this->filterLocationDetails($loc->load('vehicle')));
    }


    /**
     * @param Location $loc
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteLocation(Location $loc)
    {
        if (Gate::denies('delete-location', $loc->organisation)) {
            abort(403);
        }
        $loc->delete();
        return Response::json($loc);
    }

    /**
     * subscribe to vehicle location updates for the organisation
     */
    public function subscribe(SSE $sse)
    {
        $user = Auth::getUser();
        $org = $user->organisation;
        if (Gate::denies('view-location', $org)){
            abort(403);
        }
        Log::debug('location updates SSE request received');
        $response = $sse->createResponse();
        $sse->addEventListener('onLocationUpdate',new LocationUpdateHandler());


//        $response = new StreamedResponse(function() {
//
//            $after = (new \DateTime())->setTime(0,0);
//            $locations = Location::where('status','<>','received')
//                ->where('status','<>','queued')
//                ->whereDate('updated_at', '>=', $after);
//            while (true){
//                $nextAfter = new \DateTime();
//
//                foreach ($locations as $loc){
//                    Log::debug('sending location update '.$loc->_id);
//                    echo 'data: '.$loc->toJson."\n\n";
//                    ob_flush();
//                    flush();
//                }
//                $after = $nextAfter;
//                sleep(4);
//                Log::debug('awake and looking for location updates ');
//                $locations = Location::where('status','<>','queued')
//                    ->whereDate('updated_at', '>=', $after);
//            }

//            $channel = 'trucker-tracker.' . $org->_id;
//            Redis::subscribe([$channel], function($message) {
//                Log::debug('message read from subscribed redis channel: '.$message);
//                echo 'data: '.$message."\n\n";
//                ob_flush();
//                flush();
//            });
//        });
        Log::debug('location updates SSE in place');
        return $response;
    }


    /**
     * @param $loc
     * @return mixed
     */
    private function filterLocationDetails($loc)
    {
        $array = $loc->toArray();
        $array['vehicle'] = array_filter($array['vehicle'], function($key){
            return in_array($key,['_id','registration_number']);
        },ARRAY_FILTER_USE_KEY );
        return $array;
    }
}
