<?php

namespace TruckerTracker\Http\Controllers;

use Gate;
use Illuminate\Http\Request;

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
        $this->middleware('auth');
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
     * @param Driver $driver
     * @return \Illuminate\Http\JsonResponse
     */
    public function getConversation(Driver $driver)
    {
        if (Gate::denies('view-message', $driver->organisation)) {
            abort(403);
        }
        $driver->load(['messages'=> function($query){
            $query->orderBy('created_at');
        }]);
        $jsonResponse = Response::json($driver);
        return $jsonResponse;
    }

}
