<?php

namespace TruckerTracker\Http\Controllers;

use Auth;
use Gate;
use Illuminate\Support\Collection;
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
    public function getLocations()
    {
        $user = Auth::getUser();
        $org = $user->organisation;
        if (Gate::denies('view-location', $org)) {
            abort(403);
        }
        return Response::json($this->filterLocationDetails($org->locations()->with('vehicle')->get()));
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
     * @param $loc
     * @return mixed
     */
    private function filterLocationDetails($loc)
    {
        if ($loc instanceof Collection){
            $array = [];
            foreach ($loc as $k => $l){
                $array[] = $this->filterLocationDetails($l);
            }
        } else {
            $array = $loc->toArray();
            if (!is_null($array['vehicle'])){
                $array['vehicle'] = array_filter($array['vehicle'], function($key){
                    return !is_null($key) && in_array($key,['_id','registration_number']);
                },ARRAY_FILTER_USE_KEY );
            }
        }
        return $array;
    }
}
