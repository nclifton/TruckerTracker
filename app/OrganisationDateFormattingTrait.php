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

trait OrganisationDateFormattingTrait
{

    /**
     * @param mixed $dt
     * @param \TruckerTracker\Organisation $org
     * @return string
     */
    protected function formatDate( $dt, Organisation $org)
    {
        $dateTime = $this->translateMongoDate($dt, $org);
        return $dateTime->format('c');


    }

    /**
     * @param $dt
     * @param Organisation $org
     * @return string
     */
    protected function formatAsRelativeDateTime($dt, Organisation $org){
        $dateTime = $this->translateMongoDate($dt, $org);
        $then = $dateTime->getTimestamp();
        $now = time();
        $since = $now - $then;

        $chunks = [
            [60 * 60 * 24 * 365 , 'year'],
            [60 * 60 * 24 * 30 , 'month'],
            [60 * 60 * 24 * 7, 'week'],
            [60 * 60 * 24 , 'day'],
            [60 * 60 , 'hour'],
            [60 , 'minute'],
            [1 , 'second']
        ];

        $count = 0;
        $name='';
        for ($i = 0, $j = count($chunks); $i < $j; $i++) {
            $seconds = $chunks[$i][0];
            $name = $chunks[$i][1];
            if (($count = floor($since / $seconds)) != 0) {
                break;
            }
        }

        $print = ($count == 1) ? '1 '.$name : "$count {$name}s";
        return $print;


    }

    /**
     * @param $time1
     * @param null $time2
     * @param string $output
     * @return array|bool|mixed
     */
    function timespan($time1, $time2 = NULL, $output = 'years,months,weeks,days,hours,minutes,seconds')
    {
        // Array with the output formats
        $output = preg_split('/[^a-z]+/', strtolower((string) $output));

        // Invalid output
        if (empty($output))
            return FALSE;

        // Make the output values into keys
        extract(array_flip($output), EXTR_SKIP);

        // Default values
        $time1  = max(0, (int) $time1);
        $time2  = empty($time2) ? time() : max(0, (int) $time2);

        // Calculate timespan (seconds)
        $timespan = abs($time1 - $time2);

        // All values found using Google Calculator.
        // Years and months do not match the formula exactly, due to leap years.

        // Years ago, 60 * 60 * 24 * 365
        isset($years) and $timespan -= 31556926 * ($years = (int) floor($timespan / 31556926));

        // Months ago, 60 * 60 * 24 * 30
        isset($months) and $timespan -= 2629744 * ($months = (int) floor($timespan / 2629743.83));

        // Weeks ago, 60 * 60 * 24 * 7
        isset($weeks) and $timespan -= 604800 * ($weeks = (int) floor($timespan / 604800));

        // Days ago, 60 * 60 * 24
        isset($days) and $timespan -= 86400 * ($days = (int) floor($timespan / 86400));

        // Hours ago, 60 * 60
        isset($hours) and $timespan -= 3600 * ($hours = (int) floor($timespan / 3600));

        // Minutes ago, 60
        isset($minutes) and $timespan -= 60 * ($minutes = (int) floor($timespan / 60));

        // Seconds ago, 1
        isset($seconds) and $seconds = $timespan;

        // Remove the variables that cannot be accessed
        unset($timespan, $time1, $time2);

        // Deny access to these variables
        $deny = array_flip(array('deny', 'key', 'difference', 'output'));

        // Return the difference
        $difference = array();
        foreach ($output as $key)
        {
            if (isset($$key) AND ! isset($deny[$key]))
            {
                // Add requested key to the output
                $difference[$key] = $$key;
            }
        }

        // Invalid output formats string
        if (empty($difference))
            return FALSE;

        // If only one output format was asked, don't put it in an array
        if (count($difference) === 1)
            return current($difference);

        // Return array
        return $difference;
    }

    /**
     * @param $dt
     * @param Organisation $org
     * @return \DateTime
     */
    protected function translateMongoDate($dt, Organisation $org)
    {
        $timezone = $org->timezone ?: 'Australia/Sydney';
        if (is_a($dt, UTCDatetime::class)) {
            $dateTime = $dt->toDateTime();
        } else {
            $dateTime = new \DateTime($dt);
        }
        $dateTime->setTimezone(new \DateTimeZone($timezone));
        return $dateTime;
    }


}