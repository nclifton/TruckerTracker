<?php

namespace TruckerTracker\Http\Controllers;

use Config;
use Gate;
use Guzzle;
use Log;
use Response;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use MongoDB\BSON\UTCDatetime;
use Illuminate\Support\Facades\Redis;
use Services_Twilio_Twiml;
use TruckerTracker\Driver;
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
        $twiml = new Services_Twilio_Twiml();
        if ($driver = Driver::where('mobile_phone_number', $from)->first()) {
            Log::debug(sprintf("text message received from driver: %s" , $driver->first_name.' '.$driver->last_name));
            $org = $driver->organisation;
            $message = $this->storeMessageFromDriver($request, $org, $driver);
            $url = Config::get('url',env('APP_URL')) . '/pub/messages' . $org->_id;
            Log::debug("url: $url");
            $this->publish($url, $message->toArray(),'MessageReceived');
            Log::debug('published message');
            if ($org->auto_reply)
                $twiml->message('Thank you ' . $driver['first_name'] . ', message received');


        } else if ($vehicle = Vehicle::where('mobile_phone_number', $from)->first()) {
            Log::debug(sprintf("location text message received from vehicle: %s" , $vehicle->registration_number));
            $location = $this->updateVehicleLocation($request, $vehicle);
            if ($location) {
                $org = $vehicle->organisation;
                $url = Config::get('url',env('APP_URL')) . '/pub/locations' . $org->_id;
                Log::debug("url: $url");
                $this->publish($url, $location->toArray(),'LocationReceived');
                Log::debug('published location');
            }
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
                ->first()) {

                $this->handleMessageStatus($status, $sid, $message, $org);

            } elseif ($location = $org->locations()
                ->where('sid', $sid)
                ->first()) {

                $this->handleLocationStatus($status, $sid, $location, $org);

            }

        } catch (ModelNotFoundException $e) {
            Log::info("received a message status update for a message we didn't send");
        } catch (\Exception $e) {
            throw $e;
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
     * @return static
     */
    protected function storeMessageFromDriver(Request $request, $org, $driver)
    {
        $message = Message::create([
            'sid' => $request->MessageSid,
            'message_text' => $request->Body,
            'received_at' => $this->getUTCDatetimeNow(),
            'status' => 'received'
        ]);
        $org->messages()->save($message);
        $driver->messages()->save($message);
        $message->load('driver');
        return $message;
    }

    /**
     * @param $request
     * @param $vehicle
     */
    private function updateVehicleLocation($request, $vehicle)
    {

        $location = $vehicle->locations()->where('status', '<>', 'received')->orderby('changed_at', 'asc')->first();
        if ($location){
            $org = $vehicle->organisation;
            $location->load('vehicle');

            $messageText = $request->Body;
            $data = $this->trackerData($messageText);

            $location->update(
                [
                    'sid_response' => $request->MessageSid,
                    'latitude' => $this->latLonToFloat($data['Lat']),
                    'longitude' => $this->latLonToFloat($data['Lon']),
                    'course' => floatval($data['Course']),
                    'speed' => floatval($data['Speed']),
                    'datetime' => $this->readTrackerDatetime($data['DateTime'], $org),
                    'status' => 'received',
                    'received_at' => new \DateTime()
                ]);
        }

        return $location;
    }

    private function readTrackerDatetime($DateTime, Organisation $org)
    {
        $datetime = \DateTime::createFromFormat('y -m -d H:i:s', $DateTime);
        $datetime->setTimezone(new \DateTimeZone($org->timezone));
        return $datetime;
    }

    private function trackerData($messageText)
    {
        preg_match_all('/([^:]*):([^,]*)[,$]?/', $messageText, $matches);
        return (empty($matches))
            ? []
            : array_combine($matches[1], $matches[2]);

    }

    private function latLonToFloat($str)
    {
        return floatval(str_replace(['N', 'S', 'E', 'W'], ['', '-', '', '-'], $str));
    }

    /**
     * @param $status
     * @param $sid
     * @param $message
     * @param $org
     */
    protected function handleMessageStatus($status, $sid, $message, $org)
    {
        Log::debug(sprintf("status update (%s) received for message: %s", $status, $sid));
        $message[$status . '_at'] = new \DateTime();
        $message->update(['status' => $status]);
        $url = Config::get('url',env('APP_URL')) . '/pub/messages' . $org->_id;
        Log::debug("url: $url");
        $this->publish($url, $message->toArray(), 'MessageUpdate');

    }

    /**
     * @param $status
     * @param $sid
     * @param $location
     * @param $org
     */
    protected function handleLocationStatus($status, $sid, $location, $org)
    {
        Log::debug(sprintf("status update (%s) received for location: %s", $status, $location->_id));
        $location[$status . '_at'] = new \DateTime();
        $location->update(['status' => $status]);
        $url = Config::get('url',env('APP_URL')) . '/pub/locations' . $org->_id;
        Log::debug("url: $url");
        $this->publish($url, $location->toArray(),'LocationUpdate');

    }

    /**
     * @param $url
     * @param $pubMsg
     * @param $event
     */
    protected function publish($url, $pubMsg, $event)
    {
        Log::debug("publish event: $event data: ".json_encode($pubMsg));
        try{
            $response = Guzzle::post(
                $url,
                [
                    'headers' => [
                        'Accept'                => 'text/json',
                        'X-EventSource-Event'   => $event
                    ],
                    'json' => $pubMsg
                ]
            );
            switch ($code = $response->getStatusCode()) {
                case 200:
                    Log::debug("unexpected OK response from NGINX NCHAN Publish endpoint, : $code");
                    break;
                case 201:
                    Log::debug("Created - with at least one subscriber present, : $code");
                    break;
                case 202:
                    Log::debug("Accepted - no subscribers present, : $code");
                    break;
                default:
                    $reason = $response->getReasonPhrase();
                    Log::debug("Something broke, : $code : $reason");
            }
        } catch (\Exception $e){
            Log::critical($e->getMessage());
        }



    }
}
