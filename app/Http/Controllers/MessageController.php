<?php

namespace TruckerTracker\Http\Controllers;

use Auth;
use Gate;
use Illuminate\Http\Request;
use Log;

use TruckerTracker\Driver;
use TruckerTracker\Message;
use Response;
use TruckerTracker\Http\Requests;

class MessageController extends Controller
{
    /**
     * Create a new controller instance.
     *
     */
    public function __construct()
    {
    }

    public function deleteMessage(Message $message)
    {
        if (Gate::denies('delete-message', $message->organisation)) {
            abort(403);
        }
        $message->delete();
        return Response::json($message);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @internal param Driver $driver
     */
    public function getConversation(Request $request)
    {
        Log::debug( json_encode($request->all()));

        $user = Auth::getUser();
        if (Gate::denies('view-message', $user->organisation)) {
            abort(403);
        }
        $driverIds = $request->all();

        $messages = Message::with(['driver'=>function($query){
            $query->select('first_name','last_name');
        }])
            ->whereIn('driver_id',$driverIds)
            ->get();

        $jsonResponse = Response::json($messages);
        return $jsonResponse;
    }

}
