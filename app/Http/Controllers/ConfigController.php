<?php

namespace TruckerTracker\Http\Controllers;

use Gate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Response;
use TruckerTracker\Driver;
use TruckerTracker\Organisation;
use TruckerTracker\Twilio\TwilioHelper;
use TruckerTracker\User;
use TruckerTracker\Vehicle;

class ConfigController extends Controller
{

    public $restful = true;

    /**
     * ConfigController constructor.
     */
    public function __construct()
    {
    }

    /**
     * @param Organisation $org
     * @return \Illuminate\Http\JsonResponse
     */
    public function getOrganisation(Organisation $org)
    {
        if (Gate::denies('view-organisation', $org)) {
            abort(403);
        }
        return Response::json($this
            ->prepareOrganisationResponse($org));
    }

    /**
     *
     * create and add a new organisation
     * - creating and adding the twilio user
     * - making the logged in user the organisation's "first user"
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function addOrganisation(Request $request)
    {
        $this->validateOrganisation($request);
        $attributes = array_merge($request->all(), ['auto_reply' => false]);
        if (key_exists('hour12',$attributes)) {
            $attributes['hour12'] = $attributes['hour12'] == 1;
        } else {
            $attributes['hour12'] = false;
        }
        $this->internationalisePhoneNumbers($attributes, ['twillio_phone_number']);
        $twilioUsername = $attributes['twilio_username'];
        unset($attributes['twilio_username']);
        $org = Organisation::create($attributes);
        $user = Auth::user();
        $org->users()->save($user);
        $user->firstUserOrganisation()->save($org);
        $this->addOrganisationTwilioUser($twilioUsername, $org);

        return Response::json($this
            ->prepareOrganisationResponse($org));
    }

    /**
     *
     * create and add a new organisation user
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @internal param Organisation $org
     */
    public function addUser(Request $request)
    {
        $org = User::find(Auth::user()->_id)->organisation;

        if (Gate::denies('edit-users', $org)) {
            abort(403);
        }
        // validate user registration
        $this->validateUserRegistration($request);

        // create user
        $user = User::create([
            'name' => $request['name'],
            'email' => $request['email'],
            'password' => bcrypt($request['password']),
        ]);

        // add user to organisation
        $org->users()->save($user);

        // return the public attributes of the created user
        return Response::json($user);
    }


    /**
     * @param Organisation $org
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateOrganisation(Organisation $org, Request $request)
    {
        if (Gate::denies('update-organisation',$org)) {
            abort(403);
        }

        $this->validateOrganisation($request);
        $attributes = $request->all();
        if (key_exists('hour12',$attributes)) {
            $attributes['hour12'] = $attributes['hour12'] == 1;
        } else {
            $attributes['hour12'] = false;
        }
        $this->internationalisePhoneNumbers($attributes, ['twillio_phone_number']);

        $org->update($attributes);

        return Response::json($this
            ->prepareOrganisationResponse($org));
    }


    /**
     * @param User $user
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @internal param Organisation $org
     */
    public function updateUser(User $user, Request $request)
    {
        if (Gate::denies('edit-users',$user->organisation)) {
            abort(403);
        }
        // validate user registration
        $this->validateUserUpdate($request,$user);

        // update user
        $user->update([
            'name' => $request['name'],
            'email' => $request['email'],
            'password' => bcrypt($request['password']),
        ]);

        // return the public attributes of the user
        return Response::json($user);
    }

    /**
     * @param User $user
     * @return \Illuminate\Http\JsonResponse
     * @internal param Organisation $org
     */
    public function deleteUser(User $user)
    {
        if (Gate::denies('edit-users', $user->organisation)) {
            abort(403);
        }
        $user->delete();
        return Response::json($user);
    }

    /**
     * @param User $user
     * @return \Illuminate\Http\JsonResponse
     * @internal param Organisation $org
     */
    public function getUser(User $user)
    {
        if (Gate::denies('view-organisation', $user->organisation)) {
            abort(403);
        }
        return Response::json($user);
    }

    /**
     * @param $driver
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDriver(Driver $driver)
    {
        if (Gate::denies('view-driver', $driver->organisation)) {
            abort(403);
        }
        return Response::json($driver);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function addDriver(Request $request)
    {
        $user = Auth::user();
        if (Gate::denies('add-driver', $user->organisation)) {
            abort(403);
        }

        $this->validateDriver($request, $user);
        $driver = Driver::create($request->all());
        $user->organisation->drivers()->save($driver);
        return Response::json($driver);
    }

    /**
     * @param $driver
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateDriver(Driver $driver, Request $request)
    {
        if (Gate::denies('update-driver', $driver->organisation)) {
            abort(403);
        }
        $user = Auth::user();
        $this->validateDriver($request, $user, $driver->_id);
        $driver->update($request->all());
        return Response::json($driver);
    }

    /**
     * @param $driver
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteDriver(Driver $driver)
    {
        if (Gate::denies('delete-driver', $driver->organisation)) {
            abort(403);
        }
        $driver->delete();
        return Response::json($driver);
    }


    /**
     * @param $vehicle
     * @return \Illuminate\Http\JsonResponse
     */
    public function getVehicle(Vehicle $vehicle)
    {
        if (Gate::denies('view-driver', $vehicle->organisation)) {
            abort(403);
        }
        return Response::json($vehicle);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function addVehicle(Request $request)
    {
        $user = Auth::user();
        if (Gate::denies('add-vehicle', $user->organisation)) {
            abort(403);
        }
        $request->merge(['tracker_password'=>config('app.default_tracker_password')]);
        $this->validateVehicle($request, $user);
        $vehicle = Vehicle::create($request->all());
        $user->organisation->vehicles()->save($vehicle);
        return Response::json($vehicle);
    }

    /**
     * @param $vehicle
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateVehicle(Vehicle $vehicle, Request $request)
    {
        if (Gate::denies('update-vehicle', $vehicle->organisation)) {
            abort(403);
        }
        $user = Auth::user();
        $this->validateVehicle($request, $user, $vehicle->_id);
        $vehicle->update($request->all());
        return Response::json($vehicle);
    }

    /**
     * @param $vehicle
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteVehicle(Vehicle $vehicle)
    {
        if (Gate::denies('delete-vehicle', $vehicle->organisation)) {
            abort(403);
        }
        $vehicle->delete();
        return Response::json($vehicle);
    }

    /**
     * @param Request $request
     */
    private function validateUserRegistration(Request $request){
        $this->validate($request,[
            'name' => 'required|max:255',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|min:6|confirmed',
        ]);
    }

    /**
     * @param Request $request
     * @param User $user
     */
    private function validateUserUpdate(Request $request,User $user){
        $this->validate($request,[
            'name' => 'required|max:255',
            'email' => 'required|email|max:255|unique:users,email,'.$user->_id.',_id',
            'password' => 'sometimes|min:6|confirmed',
        ]);
    }

    /**
     * @param Request $request
     */
    private function validateOrganisation(Request $request)
    {
        $this->validate($request,
            [
                'name' =>
                    [
                        'required',
                        'max:128'
                    ],
                'twilio_account_sid' =>
                    [
                        'regex:/^AC([\da-f][\da-f]){16,16}$/'
                    ],
                'twilio_auth_token' =>
                    [
                        'regex:/^([\da-f][\da-f]){16,16}$/'
                    ],
                'twilio_phone_number' =>
                    [
                        'ausphone'
                    ],
                'timezone' =>
                    [
                        'timezone'
                    ]
            ],
            [
                'name.required' => "We do need a name for your organisation",
                'name.max' => 'That name for you organisation is too long, make it less than 128',
                'twilio_account_sid.regex' => 'That does not match the pattern of a Twilio Account SID, please check',
                'twilio_auth_token.regex' => 'That does not match the pattern of a Twilio Authentication Token, please check',
                'twilio_phone_number.ausphone' => 'That doesn\'t look like an australian phone number, it needs to have 10 digits and start with a 0 or start with +61 and have 11 digits',
                'timezone.timezone' => 'That doesn\'t look like one of our valid timezone names. Should be something like Australia/Sydney'
            ]
        );
    }


    /**
     * @param Request $request
     * @param User $user
     * @param string $cid
     */
    private function validateDriver(Request $request, User $user, $cid = 'NULL')
    {
        $org_id = $user->organisation_id;
        $ln = $request->last_name;
        $fn = $request->first_name;
        $this->validate($request,
            [
                'first_name' =>
                    [
                        'required',
                        'unique:drivers,first_name,' . $cid . ',_id,last_name,' . $ln . ',organisation_id,' . $org_id
                    ],
                'last_name' =>
                    [
                        'required',
                        'unique:drivers,last_name,' . $cid . ',_id,first_name,' . $fn . ',organisation_id,' . $org_id
                    ],
                'mobile_phone_number' =>
                    [
                        'required',
                        'ausphone',
                        'unique:drivers,mobile_phone_number,' . $cid . ',_id,organisation_id,' . $org_id
                    ],
                'drivers_licence_number' =>
                    [
                        'drvlic', 'unique:drivers,drivers_licence_number,' . $cid . ',_id,organisation_id,' . $org_id
                    ]
            ], [
                'first_name.required' => "We do need to have first names for your drivers",
                'last_name.required' => "We do need to have last names for your drivers",
                'first_name.unique' => 'you already have a driver with that first name and last name',
                'last_name.unique' => 'you already have a driver with that first name and last name',
                'mobile_phone_number.required' => "We're going to need the driver's mobile phone number",
                'mobile_phone_number.ausphone' => "That doesn't look like an australian phone number, it needs to have 10 digits and start with a 0 or start with +61 and have 11 digits",
                'mobile_phone_number.unique' => "Another one of your drivers has that same phone number, that's not going to work",
                'drivers_licence_number.drvlic' => 'That drivers licence number looks odd, please check, should be 4 to 9 alpha numeric',
                'drivers_licence_number.unique' => "Another one of your drivers has the same licence number, that's not allowed"
            ]);
    }

    /**
     * @param Request $request
     * @param User $user
     */
    private function validateVehicle(Request $request, User $user, $cid = 'NULL')
    {
        $org_id = $user->organisation_id;
        $this->validate($request,
            [
                'registration_number' =>
                    [
                        'required',
                        'regex:/^(?=.*\d)(?=.*[A-Z])[A-Z\d]{6,7}$/',
                        'unique:vehicles,registration_number,'.$cid.',_id,organisation_id,' . $org_id
                    ],
                'mobile_phone_number' =>
                    [
                        'required',
                        'ausphone',
                        'unique:vehicles,mobile_phone_number,'.$cid.',_id,organisation_id,' . $org_id
                    ],
                'tracker_imei_number' =>
                    [
                        'imei',
                        'unique:vehicles,tracker_imei_number,'.$cid.',_id,organisation_id,' . $org_id
                    ],
                'tracker_password' =>
                [
                    'regex:/\d{6}/'
                ]
            ], [
                'registration_number.required' => 'We know the vehicles by their registration numbers',
                'registration_number.regex' => 'That doesn\'t look like a normal vehicle registration plate number',
                'registration_number.unique' => 'It seems you already have a vehicle in here with that registration number',
                'mobile_phone_number.required' => 'We need the mobile phone number used by the vehicle tracker',
                'mobile_phone_number.ausphone' => "That doesn't look like an australian phone number, it needs to have 10 digits and start with a 0 or start with +61 and have 11 digits",
                'mobile_phone_number.unique' => 'Another vehicle already has that phone number',
                'tracker_imei_number.imei' => 'That doesn\'t look like an IMEI number, please check',
                'tracker_imei_number.unique' => "IMEI numbers are always unique but this one is being used on one of your other vehicles",
                'tracker_password' => 'That\' not a valid tracker password. It needs to be 6 digits.'
            ]
        );
    }

    /**
     * @param array $attributes
     * @param array $phoneNumberAttributeNames
     */
    private function internationalisePhoneNumbers($attributes, $phoneNumberAttributeNames)
    {
        foreach ($attributes as $name => $value) {
            if (in_array($name, $phoneNumberAttributeNames)) {
                if (substr($value, 0, 1) == '0') {
                    $attributes[$name] = '+61' . substr($value, 1);
                }
            }
        }

    }

    /**
     * @param $username
     * @param Organisation $org
     * @return User
     */
    private function createTwillioUser($username, Organisation $org)
    {
        $name = 'twiliouser';
        $email = preg_replace('/^[^@]*(.*)/', $name . '$1', $org->firstUser->email);
        $password = $org->twilio_user_password;
        $user = User::create([
            'name' => $name,
            'email' => $email,
            'username' => $username,
            'password' => bcrypt($password)
        ]);
        return $user;

    }

    /**
     * @param $twilioUsername
     * @param Organisation $org
     * @return User
     * @internal param $user
     */
    private function addOrganisationTwilioUser( $twilioUsername, Organisation $org)
    {
        $twilioUser = $this->createTwillioUser($twilioUsername,$org);
        $org->users()->save($twilioUser);
        $twilioUser->twilioUserOrganisation()->save($org);
        return $twilioUser;
    }

    /**
     * @param array|Organisation $org
     * @return array
     */
    private function prepareOrganisationResponse(Organisation $org)
    {

        $orgArray = $org->toArray();
        $orgArray['users'] = $org->addedUsers()->get()->toArray();
        return array_merge($orgArray,[
            'twilio_inbound_message_request_url'=>TwilioHelper::MessageRequestUrl($org),
            'twilio_outbound_message_status_callback_url'=>TwilioHelper::MessageStatusCallbackUrl($org)]);

    }


    /**
     * @param Organisation $org
     * @return Organisation
     */
    private function loadOrg(Organisation $org)
    {
        $org->load(['users' => function ($query) use ($org) {
            $query->whereNotIn('_id', [$org->twilio_user_id,$org->first_user_id]);
        }]);
        return $org;
    }



}