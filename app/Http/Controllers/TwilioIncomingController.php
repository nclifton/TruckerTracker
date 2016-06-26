<?php

namespace TruckerTracker\Http\Controllers;

use Event;
use Gate;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Log;
use MongoDB\BSON\UTCDatetime;
use MongoDB\BSON\UTDDateTime;
use Response;
use Services_Twilio_Twiml;
use TruckerTracker\Driver;
use TruckerTracker\Events\LocationUpdate;
use TruckerTracker\Http\Requests;
use TruckerTracker\Message;
use TruckerTracker\Organisation;
use TruckerTracker\Vehicle;

class TwilioIncomingController extends Controller
{
    //

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('api');
    }


    function voice()
    {
        if (Gate::denies('voice')) {
            abort(403);
        }
        $twiml = new Services_Twilio_Twiml();
        $twiml->say('Hello, McSweeney Transport Group, Trucker Tracker speaking. I have a message for you.', array('voice' => 'alice'));
        $response = Response::make($twiml, 200);
        $response->header('Content-Type', 'text/xml');
        return $response;
    }


    function message(Request $request)
    {
        if (Gate::denies('add-message')) {
            abort(403);
        }

        Log::info('received message');

        $from = $request->From;
        $twiml = new Services_Twilio_Twiml();;
        if ($driver = Driver::where('mobile_phone_number', $from)->first()) {
            $org = $driver->organisation;
            $this->storeMessageFromDriver($request, $org, $driver);
            if ($org->auto_reply)
                $twiml->message('Thank you ' . $driver['first_name'] . ', message received');
        } else if ($vehicle = Vehicle::where('mobile_phone_number', $from)->first()) {
            $location = $this->storeVehicleLocation($request, $vehicle);
            event(new LocationUpdate($location));
        } else {
            Log::info('ignoring message: unknown number: ' . $from);
        }
        $response = Response::make($twiml, 200);
        $response->header('Content-Type', 'text/xml');
        return $response;
    }


    function messageStatus(Request $request)
    {
        if (Gate::denies('update-message')) {
            abort(403);
        }
        $sid = $request->MessageSid;
        $status = $request->MessageStatus;
        $account_sid = $request->AccountSid;
        $to = $request->To;
        $from = $request->From;

        Log::info("message status received: \n"
            . 'MessageSid: ' . $sid
            . "\nMessageStatus: " . $status
            . "\nAccountSid: " . $account_sid
            . "\nTo: " . $to
            . "\nFrom: " . $from);

        try {
            $org = Organisation::where('twilio_account_sid', $account_sid)
                ->firstOrFail();

            if ($message = $org->messages()
                ->where('sid', $sid)
                ->first()){
                Log::info("message: ".$sid);
                $message->update(['status' => $status]);
            } elseif ($location = $org->locations()
                ->where('sid', $sid)){
                Log::info("location: ".$sid);
                $location->update(['status' => $status]);
                event(new LocationUpdate($location));
            }

        } catch (ModelNotFoundException $e) {
            Log::info("received a message status update for a message we didn't send");
        }

        return Response::json(['status' => 'received']);
    }


    /**
     * @return UTCDatetime
     */
    protected function now()
    {
        return new UTCDateTime(round(microtime(true) * 1000));
    }

    /**
     * @param Request $request
     * @param $org
     * @param $driver
     */
    protected function storeMessageFromDriver(Request $request, $org, $driver)
    {
        $message = Message::create([
            'sid' => $request->MessageSid,
            'message_text' => $request->Body,
            'from' => $request->From,
            'received_at' => $this->getUTCDatetimeNow(),
            'status' => 'received'
        ]);
        $org->messages()->save($message);
        $driver->messages()->save($message);
    }

    /**
     * @param $request
     * @param $vehicle
     */
    private function storeVehicleLocation($request, $vehicle)
    {

        $location = $vehicle->locations()->where('status', '<>', 'received')->orderby('sent_at', 'desc')->first();
        $org = $vehicle->organisation;

        $messageText = $request->Body;
        $data = $this->trackerData($messageText);

        $location->update(
            [
                'sid_response' => $request->MessageSid,
                'latitude' => $this->latLonToFloat($data['Lat']),
                'longitude' => $this->latLonToFloat($data['Lon']),
                'course' => floatval($data['Course']),
                'speed' => floatval($data['Speed']),
                'datetime' => $this->readTrackerDatetime($data['DateTime'],$org),
                'status' => 'received'
            ]);
        return $location;
    }

    private function readTrackerDatetime($DateTime,Organisation $org)
    {
        $datetime = \DateTime::createFromFormat('y -m -d H:i:s', $DateTime);
        $datetime->setTimezone(new \DateTimeZone($org->timezone));
        return $datetime; // new UTCDatetime(floor($datetime->getTimestamp() * 1000));
    }

    private function trackerData($messageText)
    {
        preg_match_all('/([^:]*):([^,]*)[,$]?/', $messageText, $matches);
        return (empty($matches))
            ?[]
            :array_combine($matches[1],$matches[2]);

    }

    private function latLonToFloat($str)
    {
        return floatval(str_replace(['N','S','E','W'],['','-','','-'],$str));
    }
}
