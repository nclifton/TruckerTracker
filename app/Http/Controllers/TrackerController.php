<?php

namespace TruckerTracker\Http\Controllers;

use Illuminate\Http\Request;

use Response;
use TruckerTracker\Http\Requests;
use Log;

class TrackerController extends Controller
{

    /**
     * Create a new controller instance.
     *
     */
    public function __construct()
    {
    }

    public function storeLocation(Request $request){

        Log::debug( json_encode($request));

        return Response::make('',200);
    }
    
    
}
