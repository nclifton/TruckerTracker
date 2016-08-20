<?php
/**
 *
 * @version 0.0.1: MongoDbSetup.php 5/06/2016T15:51
 * @author Clifton IT Foundries Pty Ltd
 * @link http://cliftonwebfoundry.com.au
 * @copyright Copyright (c) 2016 Clifton IT Foundries Pty Ltd. All rights Reserved
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 *
 **/

namespace TruckerTracker;

if (!trait_exists(TestDataTrait::class))
    include 'TestDataTrait.php';

use DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;

Trait TestTrait
{

    use TestDataTrait;

    protected $database = 'trucker_tracker';
    protected $db_username = 'trucker_tracker';
    protected $db_password = '6iSgcH2eNE';
    protected $db_host = 'local.truckertracker.services';
    protected $db_port = 27017;

    protected $connection;

    protected $dataSet;

    private $twilioUser;

    abstract protected function artisanSeedDb();

    /**
     * @param Organisation $org
     * @return mixed
     */
    protected function twilioUser(Organisation $org = null)
    {

        if (is_null($this->twilioUser)) {
            $this->twilioUser = $this->user($org);
            $this->twilioUser->username = bin2hex(random_bytes(16));
            $this->orgSet[0]['twilio_user_password'] = bin2hex(random_bytes(16));
            $this->twilioUser->password = bcrypt($this->orgSet[0]['twilio_user_password']);
            $this->twilioUser->save();
            try {
                if (is_null($org))
                    $org = $this->twilioUser->organisation;
                $this->twilioUser->twilioUserOrganisation()->save($org);
                $org->twilio_user_password = $this->orgSet[0]['twilio_user_password'];
                $org->save();
            } catch (ModelNotFoundException $e) {
            }
        }
        return $this->twilioUser;
    }

    /**
     * @param Organisation $org
     * @return mixed
     */
    protected function firstUser(Organisation $org = null)
    {

        $user = $this->user($org);
        if (is_null($org))
            $org = $user->organisation;
        try {
            $user->firstUserOrganisation()->save($org);
        } catch (ModelNotFoundException $e) {
        }
        $this->twilioUser = $this->twilioUser($org);
        return $user;
    }

    /**
     * @param Organisation $org
     * @return mixed
     */
    protected function user(Organisation $org = null)
    {
        $user = factory(User::class)->create();
        try {
            $uorg = $org ?: Organisation::where('_id', $this->orgSet[0]['_id'])->firstOrFail();
            $uorg->users()->save($user);
        } catch (ModelNotFoundException $e) {
        }
        return $user;
    }

    /**
     * @param $fixture
     * @return mixed
     */
    protected function setMongoDates($fixture)
    {
        foreach ($fixture as $key => $value) {
            if (is_array($value)) {
                $fixture[$key] = $this->setMongoDates($value);
            } else if (in_array($key, ['queued_at', 'sent_at', 'received_at', 'delivered_at', 'datetime'])) {
                $fixture[$key] = ($value instanceof \DateTime)
                    ? $this->localDateTimeToMongoDate($value)
                    : $this->iso8601LocalStringToMongoDate($value);
            }
        }
        return $fixture;
    }


    /**
     * @param $value
     * @return MongoDate
     */
    protected function localDateTimeToMongoDate($value)
    {
        return new \MongoDate($this->localDateTimeToMongoUTCDateTime($value));
    }

    /**
     * @param $value
     * @return MongoDate
     */
    protected function iso8601LocalStringToMongoDate($value)
    {
        return new \MongoDate($this->iso8601LocalStringToMongoUTCDatetime($value));
    }

    /**
     * @param $value
     * @return \MongoDB\BSON\UTCDatetime
     */
    protected function iso8601LocalStringToMongoUTCDatetime($value)
    {
        $dateTime = new \DateTime($value);
        return $this->localDateTimeToMongoUTCDateTime($dateTime);
    }

    /**
     * @param $dateTime
     * @return \MongoDB\BSON\UTCDatetime
     */
    protected function localDateTimeToMongoUTCDateTime($dateTime)
    {
        $dateTime->setTimezone(new \DateTimeZone('UTC'));
        $mongoDate = new \MongoDB\BSON\UTCDatetime(round($dateTime->getTimestamp() * 1000));
        return $mongoDate;
    }

    /**
     * Assert that a given where condition exists in the database.
     *
     * @param  string $table
     * @param  array $data
     * @param  string $connection
     * @return $this
     */
    public function seeInDatabase($table, array $data, $connection = null)
    {

        $count = DB::collection($table)->where($data)->count();

        $this->assertGreaterThan(0, $count, sprintf(
            'Unable to find row in database table [%s] that matched attributes [%s].', $table, json_encode($data)
        ));

        return $this;
    }

    /**
     * Assert that a given where condition does not exist in the database.
     *
     * @param  string $table
     * @param  array $data
     * @param  string $connection
     * @return $this
     */
    public function notSeeInDatabase($table, array $data, $connection = null)
    {

        $count = DB::collection($table)->where($data)->count();

        $this->assertEquals(0, $count, sprintf(
            'Was able to find row in database table [%s] that matched attributes [%s].', $table, json_encode($data)
        ));

        return $this;
    }


}