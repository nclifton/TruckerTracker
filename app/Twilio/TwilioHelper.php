<?php
/**
 *
 * @version 0.0.1: ${FILE_NAME} 20/06/2016T20:33
 * @author Clifton IT Foundries Pty Ltd
 * @link http://cliftonwebfoundry.com.au
 * @copyright Copyright (c) 2016 Clifton IT Foundries Pty Ltd. All rights Reserved
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 *
 **/


namespace TruckerTracker\Twilio;

include_once __DIR__.DIRECTORY_SEPARATOR.'http_build_url.php';

use TruckerTracker\Organisation;

class TwilioHelper
{

    public static function MessageRequestUrl($username, $password=null)
    {
        return http_build_url(self::getUrl($username, $password),['path'=>'/incoming/message']);
    }

    public static function MessageStatusCallbackUrl($username, $password=null)
    {
        return http_build_url(self::getUrl($username, $password),['path'=>'/incoming/message/status']);
    }


    /**
     * @param $username
     * @param $password
     * @return array
     */
    protected static function getCredentials($username, $password=null)
    {
        if (is_a($username, Organisation::class)) {
            $org = $username;
            $twilioUser = $org->twilioUser()->first();
            $username = $twilioUser->username;
            $password = $org->twilio_user_password;
        }
        return [$username,$password];
    }

    /**
     * @param $username
     * @param $password
     * @return array|string
     */
    protected static function getUrl($username, $password=null)
    {
        list($username, $password) = self::getCredentials($username, $password);
        $url = http_build_url('', [
            'user' => $username,
            'pass' => $password,
            'scheme' => env('URL_SCHEME', 'http'),
            'host' => env('SERVER_DOMAIN_NAME', 'example.com')
        ]);
        return $url;
    }
}