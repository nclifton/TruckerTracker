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

use Log;
use TruckerTracker\Organisation;

class TwilioHelper
{

    public static function MessageRequestUrl($username, $password=null)
    {
        $parts = [];
        $url1 = self::getUrl($username, $password);
        $url = http_build_url(
            $url1,
            ['path' => '/incoming/message'],
            HTTP_URL_REPLACE,
            $parts);
        Log::debug('MessageRequestUrl() ',['url-from-getUrl()'=>$url1,'url-after'=>$url,'parts'=>$parts]);
        return $url;
    }

    public static function MessageStatusCallbackUrl($username, $password=null)
    {
        $parts = [];
        $url1 = self::getUrl($username, $password);
        $url = http_build_url(
            $url1,
            ['path' => '/incoming/message/status'],
            HTTP_URL_REPLACE,$parts);
        Log::debug('MessageRequestUrl() ',['url-from-getUrl()'=>$url1,'url-after'=>$url,'parts'=>$parts]);
        return $url;
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
            $username = $twilioUser->username.'';
            $password = $org->twilio_user_password.'';
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

        list($tUsername, $tPassword) = self::getCredentials($username, $password);

        Log::debug('getUrl() parts:',[
            'user' => $tUsername,
            'pass' => $tPassword,
            'scheme' => config('app.external_scheme', 'not configured'),
            'host' => config('app.external_host', 'not configured'),
            'port'=>config('app.external_port', 'not configured')
        ]);

        $urlParts = [
            'user' => $tUsername,
            'pass' => $tPassword,
            'scheme' => config('app.external_scheme', 'http'),
            'host' => config('app.external_host', 'example.com'),
        ];

        if (!empty(config('app.external_port', ''))){
            $urlParts['port']=config('app.external_port', '');
        }
        $url = http_build_url('', $urlParts);
        return $url;
    }
}