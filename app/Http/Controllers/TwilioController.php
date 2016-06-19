<?php

namespace TruckerTracker\Http\Controllers;

use Carbon\Carbon;
use TruckerTracker\Twilio\TwilioInterface;
use Twilio;
use App;
use Gate;
use Illuminate\Http\Request;
use Log;
use MongoDB\BSON\UTCDatetime;
use Response;
use Services_Twilio_RestException;
use Services_Twilio_Twiml;
use TruckerTracker\Driver;
use TruckerTracker\Http\Requests;
use TruckerTracker\Message;
use TruckerTracker\Vehicle;
use TruckerTracker\Location;
use TruckerTracker\Organisation;
use MongoDB\BSON\UTDDateTime;


/**
 * @property UTCDatetime queued_at
 * @property array|mixed|null status
 */
class TwilioController extends Controller
{
    protected $twilio;



    /**
     * Create a new controller instance.
     * @param TwilioInterface $twilio
     */
    public function __construct(\TruckerTracker\Twilio\TwilioInterface $twilio)
    {
        $this->twilio = $twilio;
        //$this->middleware('auth');
    }

    /**
     * @param Driver $driver
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws Services_Twilio_RestException
     */
    public function messageDriver(Driver $driver, Request $request)
    {
        if (Gate::denies('send-message',$driver->organisation)){
            abort(403);
        }
        $message = Message::create($request->all());
        $org = $driver->organisation;
        $org->messages()->save($message);
        $driver->messages()->save($message);
        try {

            $response = $this->text($driver->mobile_phone_number, $message->message_text, $org, $request->root());
            $message->queued_at = Carbon::now(); // new \DateTime(); //$this->getUTCDatetimeNow();
            $message->status = $response->status;
            $message->sid = $response->sid;
            $message->account_sid = $response->account_sid;
            $message->update();
            $array = array_merge(
                $message->toArray(),
                ['driver' => $driver->toArray()]
            );

            return Response::json($array);
        } catch (Services_Twilio_RestException $e) {
            Throw $e;
        }
    }

    /**
     * @param Vehicle $vehicle
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws Services_Twilio_RestException
     */
    function locateVehicle(Vehicle $vehicle, Request $request)
    {
        if (Gate::denies('send-location',$vehicle->organisation)){
            abort(403);
        }
        $location = Location::create($request->all());
        $org = $vehicle->organisation;
        $org->locations()->save($location);
        $vehicle->locations()->save($location);
        try {
            $response = $this->text($vehicle->mobile_phone_number,
                $this->requestLocationMessage($vehicle),
                $org,
                $request->root());
            $location->queued_at = Carbon::now(); // new \DateTime();// $this->getUTCDatetimeNow();
            $location->status = $response->status;
            $location->update();

            $array = array_merge(
                $location->toArray(),
                ['vehicle' => $vehicle->toArray()]
            );
            return Response::json($array);
        } catch (Services_Twilio_RestException $e) {
            Throw $e;
        }
    }

    /**
     * @param $sendToNumber
     * @param $message_text
     * @param $org
     * @return \Services_Twilio_Rest_Message
     * @internal param $message
     */
    private function text($sendToNumber, $message_text, $org, $rootUrl)
    {
        $url = parse_url($rootUrl);
        $host = env('CALLBACK_SERVER_NAME',$url['host']);

        $this->twilio->setSid($org->twilio_account_sid);
        $this->twilio->setToken($org->twilio_auth_token);
        $this->twilio->setFrom($org->twilio_phone_number);
        $client = $this->twilio->getTwilio();

        $twilioUser = $org->twilioUser;

        $creds = rawurlencode($twilioUser->email) . ':' . rawurlencode($org->twilio_user_password);

        $m = $client->account->messages->create([
            'To' => $sendToNumber,
            'From' => $org->twilio_phone_number,
            'Body' => $message_text,
            'StatusCallback' => $url['scheme'].'://'.$creds.'@'.$host."/incoming/message/status"
        ]);

        // Return the message object to the browser as JSON
        return $m;
    }


    /**
     * @param $org
     * @return string
     */
    protected function sent_at($org)
    {
        $timezone = $org->timezone ?: 'Australia/Sydney';
        $datetime_format = $org->datetime_format ?: 'd/m/y H:i:s';
        $dateTime = new \DateTime('now', new \DateTimeZone($timezone));
        $str = $dateTime->format($datetime_format);
        return $str;
    }

    private function requestLocationMessage($vehicle)
    {
        return "WHERE,${vehicle['tracker_password']}#";
    }

}
