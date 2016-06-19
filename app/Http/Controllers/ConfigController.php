<?php

namespace TruckerTracker\Http\Controllers;

use Gate;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\AuthenticatesAndRegistersUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Response;
use TruckerTracker\Driver;
use TruckerTracker\Http\Requests;
use TruckerTracker\Organisation;
use TruckerTracker\User;
use TruckerTracker\Vehicle;

class ConfigController extends Controller
{

    use AuthenticatesAndRegistersUsers;
    
    public $restful = true;

    /**
     * ConfigController constructor.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * @param Organisation $org
     * @return \Illuminate\Http\JsonResponse
     */
    public function getOrganisation($org)
    {
        if (Gate::denies('view-organisation', $org)) {
            abort(403);
        }
        return Response::json($this->filterOrganisationResponse($this->loadOrg($org)));
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
        $this->internationalisePhoneNumbers($attributes, ['twillio_phone_number']);
        $org = Organisation::create($attributes);
        $user = Auth::user();
        $org->users()->save($user);
        $user->firstUserOrganisation()->save($org);
        $this->addOrganisationTwilioUser($user, $org);

        return Response::json($this->filterOrganisationResponse($this->loadOrg($org)));
    }

    /**
     *
     * create and add a new organisation user
     *
     * @param Organisation $org
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function addOrganisationUser(Organisation $org, Request $request)
    {
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
        $this->internationalisePhoneNumbers($attributes, ['twillio_phone_number']);

        $org->update($attributes);

        return Response::json($this->filterOrganisationResponse($this->loadOrg($org)));
    }


    /**
     * @param Organisation $org
     * @param User $user
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateOrganisationUser(User $user, Request $request)
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
     * @param Organisation $org
     * @param User $user
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function deleteOrganisationUser(User $user)
    {
        if (Gate::denies('edit-users', $user->organisation)) {
            abort(403);
        }
        $user->delete();
        return Response::json($user);
    }

    /**
     * @param Organisation $org
     * @param User $user
     * @return \Illuminate\Http\JsonResponse
     */
    public function getOrganisationUser(User $user)
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
    public function getDriver($driver)
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
    public function updateDriver($driver, Request $request)
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
    public function deleteDriver($driver)
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
    public function getVehicle($vehicle)
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
    public function updateVehicle($vehicle, Request $request)
    {
        if (Gate::denies('update-vehicle', $vehicle->organisation)) {
            abort(403);
        }
        $user = Auth::user();
        $this->validateVehicle($request, $user);
        $vehicle->update($request->all());
        return Response::json($vehicle);
    }

    /**
     * @param $vehicle
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteVehicle($vehicle)
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
            'password' => 'sometimes|required|min:6|confirmed',
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
     * @param $user
     * @param string $cid
     */
    private function validateDriver(Request $request, $user, $cid = 'NULL')
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
     * @param $user
     */
    private function validateVehicle(Request $request, $user)
    {
        $org_id = $user->organisation_id;
        $this->validate($request,
            [
                'registration_number' =>
                    [
                        'required',
                        'regex:/^(?=.*\d)(?=.*[A-Z])[A-Z\d]{6,7}$/',
                        'unique:vehicles,registration_number,NULL,_id,organisation_id,' . $org_id
                    ],
                'mobile_phone_number' =>
                    [
                        'required',
                        'ausphone',
                        'unique:vehicles,mobile_phone_number,NULL,_id,organisation_id,' . $org_id
                    ],
                'tracker_imei_number' =>
                    [
                        'imei',
                        'unique:vehicles,tracker_imei_number,NULL,_id,organisation_id,' . $org_id
                    ]
            ], [
                'registration_number.required' => 'We know the vehicles by their registration numbers',
                'registration_number.regex' => 'That doesn\'t look like a normal vehicle registration plate number',
                'registration_number.unique' => 'It seems you already have a vehicle in here with that registration number',
                'mobile_phone_number.required' => 'We need the mobile phone number used by the vehicle tracker',
                'mobile_phone_number.ausphone' => "That doesn't look like an australian phone number, it needs to have 10 digits and start with a 0 or start with +61 and have 11 digits",
                'mobile_phone_number.unique' => 'Another vehicle already has that phone number',
                'tracker_imei_number.imei' => 'That doesn\'t look like an IMEI number, please check',
                'tracker_imei_number.unique' => "IMEI numbers are always unique but this one is being used on one of your other vehicles"
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
     * @param User $user
     * @return array
     */
    private function createTwillioUser(User $user)
    {
        $name = 'twiliouser';
        $email = preg_replace('/^[^@]*(.*)/', $name . '$1', $user->email);
        $password = bin2hex(random_bytes(16));
        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => bcrypt($password)
        ]);
        return [$user, $password];

    }

    /**
     * @param User $user
     * @param Organisation $org
     * @return User
     */
    private function addOrganisationTwilioUser(User $user, Organisation $org)
    {
        list($twilioUser, $password) = $this->createTwillioUser($user);
        $org->users()->save($twilioUser);
        $twilioUser->twilioUserOrganisation()->save($org);
        $org->update(['twilio_user_password' => $password]);
        return $twilioUser;
    }

    /**
     * @param array|Organisation $org
     * @return array
     */
    private function filterOrganisationResponse($org)
    {
        $orgArray = (is_a($org,Model::class)) ? $org->toArray() : $org;

        $filterOutKeys = [
            'first_user',
            'twilio_user'
        ];
        return array_filter($orgArray, function ($key) use($filterOutKeys){
            return !in_array($key,$filterOutKeys);
        },ARRAY_FILTER_USE_KEY);
    }


    /**
     * @param Organisation $org
     * @return Organisation
     */
    private function loadOrg(Organisation $org)
    {
        $org->load(['users' => function ($query) use ($org) {
            $query->where('_id', '<>', $org->twilio_user_id)
                ->where('_id', '<>', $org->first_user_id);
        }]);
        return $org;
    }



}