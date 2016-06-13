<?php
/**
 *
 * @version 0.0.1: Twilio.php 12/06/2016T01:26
 * @author Clifton IT Foundries Pty Ltd
 * @link http://cliftonwebfoundry.com.au
 * @copyright Copyright (c) 2016 Clifton IT Foundries Pty Ltd. All rights Reserved
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 *
 **/


namespace TruckerTracker\Twilio;


class Twilio extends \Aloha\Twilio\Twilio implements TwilioInterface
{

    public function __construct()
    {
        parent::__construct(null, null, null, null);
    }


    /**
     * @param $sid
     * @return TwilioInterface
     */
    public function setSid($sid)
    {
        $this->sid = $sid;
    }

    /**
     * @param $token
     * @return TwilioInterface
     */
    public function setToken($token)
    {
        $this->token = $token;
    }

    /**
     * @param $from
     * @return TwilioInterface
     */
    public function setFrom($from)
    {
        $this->from = $from;
    }
}