<?php

namespace TruckerTracker\Http\Controllers;

use Auth;
use Gate;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Redis;
use Log;
use Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use TruckerTracker\Events\LocationUpdate;
use TruckerTracker\Http\Requests;
use TruckerTracker\Listeners\ClientLocationUpdater;
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


    private function filterLocationDetails($loc)
    {
        $array = $loc->toArray();
        $array['vehicle'] = array_filter($array['vehicle'], function($key){
            return in_array($key,['_id','registration_number']);
        },ARRAY_FILTER_USE_KEY );
        return $array;
    }
}
