<?php

namespace TruckerTracker\Http\Controllers;

use Gate;
use Illuminate\Http\Request;

use Response;
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

    private function filterLocationDetails($loc)
    {
        $array = $loc->toArray();
        $array['vehicle'] = array_filter($array['vehicle'], function($key){
            return in_array($key,['_id','registration_number']);
        },ARRAY_FILTER_USE_KEY );
        return $array;
    }
}
