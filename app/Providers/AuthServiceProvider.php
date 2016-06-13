<?php

namespace TruckerTracker\Providers;

use Illuminate\Contracts\Auth\Access\Gate as GateContract;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use TruckerTracker\Organisation;
use TruckerTracker\User;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        'TruckerTracker\Model' => 'TruckerTracker\Policies\ModelPolicy',
    ];

    private static function isOrganisationUser($user, $org)
    {
        return (!empty($org) && (!empty($user->organisation)) && $user->organisation->_id === $org->_id);
    }

    private static function isTwilioUser($user, $org)
    {
        return (!empty($org) && !empty($org->twilioUser) && $org->twilioUser->_id === $user->_id);
    }

    private static function isFirstUser(User $user, Organisation $org)
    {
        return (empty($org) || (!empty($org->firstUser) && $org->firstUser->_id === $user->_id));
     }

    /**
     * Register any application authentication / authorization services.
     *
     * @param  \Illuminate\Contracts\Auth\Access\Gate  $gate
     * @return void
     */
    public function boot(GateContract $gate)
    {
        $this->registerPolicies($gate);


        $gate->define('view-organisation', function (User $user, Organisation $org){
             return self::isFirstUser($user, $org);
        });
        $gate->define('update-organisation', function (User $user, Organisation $org){
            return self::isFirstUser($user, $org);
        });
        $gate->define('view-driver', function (User $user, Organisation $org){
            return self::isFirstUser($user, $org) || (self::isOrganisationUser($user, $org) && !(self::isTwilioUser($user, $org))) ;
        });
        $gate->define('add-driver', function (User $user, Organisation $org){
            return self::isFirstUser($user, $org);
        });
        $gate->define('update-driver', function (User $user, Organisation $org){
            return self::isFirstUser($user, $org);
        });
        $gate->define('delete-driver', function (User $user, Organisation $org){
            return self::isFirstUser($user, $org);
        });
        $gate->define('view-vehicle', function (User $user, Organisation $org){
            return self::isFirstUser($user, $org) || (self::isOrganisationUser($user,$org) && !self::isTwilioUser($user, $org));
        });
        $gate->define('add-vehicle', function (User $user, Organisation $org){
            return self::isFirstUser($user, $org);
        });
        $gate->define('update-vehicle', function (User $user, Organisation $org){
            return self::isFirstUser($user, $org);
        });
        $gate->define('delete-vehicle', function (User $user, Organisation $org){
            return self::isFirstUser($user, $org);
        });
        $gate->define('view-message', function (User $user, Organisation $org){
            return self::isFirstUser($user, $org) || (self::isOrganisationUser($user,$org) && !self::isTwilioUser($user, $org));
        });
        $gate->define('send-message', function (User $user, Organisation $org){
            return self::isFirstUser($user, $org) || (self::isOrganisationUser($user,$org) && !self::isTwilioUser($user, $org));
        });
        $gate->define('delete-message', function (User $user, Organisation $org){
            return self::isFirstUser($user, $org);
        });
        $gate->define('view-location', function (User $user, Organisation $org){
            return self::isFirstUser($user, $org) || (self::isOrganisationUser($user,$org) && !self::isTwilioUser($user, $org));
        });
        $gate->define('send-location', function (User $user, Organisation $org){
            return self::isFirstUser($user, $org) || (self::isOrganisationUser($user,$org) && !self::isTwilioUser($user, $org));
        });
        $gate->define('delete-location', function (User $user, Organisation $org){
            return self::isFirstUser($user, $org);
        });
        $gate->define('voice', function (User $user, Organisation $org = null){
            return self::isTwilioUser($user, $org?:$user->organisation);
        });
        $gate->define('add-message', function (User $user, Organisation $org = null){
            return self::isTwilioUser($user, $org?:$user->organisation);
        });
        $gate->define('update-message', function (User $user, Organisation $org = null){
            return self::isTwilioUser($user, $org?:$user->organisation);
        });

    }
}
