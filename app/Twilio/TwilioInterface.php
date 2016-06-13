<?php
/**
 *
 * @version 0.0.1: TwilioInterface.php 12/06/2016T01:18
 * @author Clifton IT Foundries Pty Ltd
 * @link http://cliftonwebfoundry.com.au
 * @copyright Copyright (c) 2016 Clifton IT Foundries Pty Ltd. All rights Reserved
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 *
 **/


namespace TruckerTracker\Twilio;


use Services_Twilio;

interface TwilioInterface extends \Aloha\Twilio\TwilioInterface
{
    /**
     * @param $sid
     * @return TwilioInterface
     */
    public function setSid($sid);


    /**
     * @param $token
     * @return TwilioInterface
     */
    public function setToken($token);


    /**
     * @param $from
     * @return TwilioInterface
     */
    public function setFrom($from);

    /**
     * @return Services_Twilio
     */
    public function getTwilio();
}