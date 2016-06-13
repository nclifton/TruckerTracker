<?php
/**
 *
 * @version 0.0.1: ${FILE_NAME} 9/06/2016T22:28
 * @author Clifton IT Foundries Pty Ltd
 * @link http://cliftonwebfoundry.com.au
 * @copyright Copyright (c) 2016 Clifton IT Foundries Pty Ltd. All rights Reserved
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 *
 **/


namespace TruckerTracker;


use Carbon\Carbon;
use MongoDB\BSON\UTCDatetime;

trait OrganisationDateFormatingTrait
{

    /**
     * @param mixed $dt
     * @param \TruckerTracker\Organisation $org
     */
    protected function formatDate( $dt, Organisation $org)
    {
        $timezone = $org->timezone ?: 'Australia/Sydney';
        $datetime_format = $org->datetime_format ?: 'H:i:s d/m/y';
        if (is_a($dt,UTCDatetime::class)){
            $dateTime =$dt->toDateTime();
        } else {
            $dateTime = new \DateTime($dt);
        }
        $dateTime->setTimezone(new \DateTimeZone($timezone));
        return $dateTime->format($datetime_format);
    }
}