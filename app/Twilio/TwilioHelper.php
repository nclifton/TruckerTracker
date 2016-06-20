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


use TruckerTracker\Organisation;

class TwilioHelper
{

    public static function MessageRequestUrl(Organisation $org)
    {
        return self::serverRootUrl($org) . '/incoming/message';
    }

    public static function MessageStatusCallbackUrl($org)
    {
        return self::serverRootUrl($org) . '/incoming/message/status';
    }

    /**
     * @param Organisation $org
     * @return string
     */
    protected static function serverRootUrl(Organisation $org)
    {
        return env('URL_SCHEME', 'http') . '://'
        . $org->twilioUser->username . ':' . $org->twilio_user_password . '@'
        . env('SERVER_DOMAIN_NAME', 'example.com');
    }
}