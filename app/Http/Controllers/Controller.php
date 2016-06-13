<?php

namespace TruckerTracker\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesResources;
use MongoDB\BSON\UTCDatetime;

class Controller extends BaseController
{
    use AuthorizesRequests, AuthorizesResources, DispatchesJobs, ValidatesRequests;

    /**
     * @return UTCDatetime
     */
    protected function getUTCDatetimeNow()
    {
        return new UTCDateTime(round(microtime(true) * 1000));
    }
}
