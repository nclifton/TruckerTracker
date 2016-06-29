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

use DB;
use Zumba\PHPUnit\Extensions\Mongo\Client\Connector;
use Zumba\PHPUnit\Extensions\Mongo\DataSet\DataSet;

Trait TestTrait
{
    use \Zumba\PHPUnit\Extensions\Mongo\TestTrait;

    protected $database = 'trucker_tracker';
    protected $db_username = 'trucker_tracker';
    protected $db_password = '6iSgcH2eNE';
    protected $db_host = 'localhost';
    protected $db_port = 27017;


    /**
     * @var \Zumba\PHPUnit\Extensions\Mongo\Client\Connector
     */
    protected $connection;
    /**
     * @var \Zumba\PHPUnit\Extensions\Mongo\DataSet\DataSet
     */
    protected $dataSet;

    protected $loginUserSet = [
        [
            'name' => 'login user 1',
            'email' => 'test1@cliftonwebfoundry.com.au',
            'password' => 'mstgpwd1'
        ], [
            'name' => 'login user 2',
            'email' => 'test2@cliftonwebfoundry.com.au',
            'password' => 'mstgpwd1'
        ], [
            'name' => 'login user 3',
            'email' => 'test3@cliftonwebfoundry.com.au',
            'password' => 'mstgpwd1'
        ]
    ];

    private $twilioUser;

    protected $fixtureUserset = [
        [
            '_id' => '100',
            'name' => 'FirstUser',
            'email' => 'test1@cliftonwebfoundry.com.au',
            'password' => '$2y$10$NkvhsSZvHX57Bm993h0ddeXdCrHwQ/X4idWV.pojZU9j3hDMmx2RG',
            'organisation_id' => '10001'
        ],
        [
            '_id' => '101',
            'name' => 'twilioUser',
            'username' => 'twiliouser',
            'email' => 'test2@cliftonwebfoundry.com.au',
            'password' => '$2y$10$NkvhsSZvHX57Bm993h0ddeXdCrHwQ/X4idWV.pojZU9j3hDMmx2RG',
            'organisation_id' => '10001'
        ],
        [
            '_id' => '102',
            'name' => 'OpsUser',
            'email' => 'test3@cliftonwebfoundry.com.au',
            'password' => '$2y$10$NkvhsSZvHX57Bm993h0ddeXdCrHwQ/X4idWV.pojZU9j3hDMmx2RG',
            'organisation_id' => '10001'
        ]

    ];

    protected $orgset = [
        [
            '_id' => '10001',
            'first_user_id' => '100',
            'twilio_user_id' => '101',
            'name' => 'McSweeney Transport Group',
            'timezone' => 'Australia/Sydney',
            'datetime_format' => 'h:i:s A D d/m/y',
            'twilio_account_sid' =>   'AC392e8d8bc564eb45ea67cc0f3a8ebf3c',
            'twilio_auth_token' =>    '36c8ee5499df1e116aa53b1ee05ca5fa',
            'twilio_phone_number' =>  '+15005550006',
            'twilio_user_password' => 'mstgpwd1',
            'auto_reply' => true
        ], [
            '_id' => '10002',
            'name' => 'Some Other Organisation',
            'timezone' => 'Australia/Sydney',
            'datetime_format' => 'H:i:s d/m/y',
            'twilio_account_sid' =>   'someOtherAccountSID',
            'twilio_auth_token' =>    '36c8ee5499df1e116aa53b1ee05ca5fa',
            'twilio_phone_number' =>  '+15005550006',
            'twilio_user_password' => 'mstgpwd1',
            'auto_reply' => false
        ]
    ];

    protected $driverset = [
        [
            '_id' => '110001',
            'first_name' => 'Driver',
            'last_name' => 'One',
            'mobile_phone_number' => '+61419140683',
            'drivers_licence_number' => '9841YG',
            'organisation_id' => '10001',

        ],
        [
            '_id' => '110002',
            'first_name' => 'Driver',
            'last_name' => 'Two',
            'mobile_phone_number' => '0298204732',
            'drivers_licence_number' => '9401HG',
            'organisation_id' => '10001',

        ]
    ];

    protected $vehicleset = [
        [
            '_id' => '120001',
            'registration_number' => 'DD6664',
            'mobile_phone_number' => '+61417673377',
            'tracker_imei_number' => '355054/06/051610/4',
            'tracker_password' => '666666',
            'organisation_id' => '10001'
        ],
        [
            '_id' => '120002',
            'registration_number' => 'SOY067',
            'mobile_phone_number' => '0298204732',
            'tracker_imei_number' => '1234567890123456',
            'tracker_password' => '666666',
            'organisation_id' => '10001'
        ]
    ];

    protected $messageSet = [
        [
            '_id' => '200001',
            'organisation_id' => '10001',
            'driver_id' => '110001',
            'message_text' => 'hello',
            'queued_at' => '2016-06-09T20:45:10+10:00',
            'sent_at' => '2016-06-09T20:46:10+10:00',
            'status' => 'sent',
            'sid' => '1111111'
        ]
    ];

    protected $locationSet = [
        [
            '_id' => '300001',
            'organisation_id' => '10001',
            'vehicle_id' => '120001',
            'queued_at' => '2016-06-09T20:55:10+10:00',
            'sent_at' => '2016-06-09T20:56:10+10:00',
            'status' => 'sent',
            'sid' => '2222222',
            'latitude' => '0.0',
            'longitude' => '0.0',
            'course' => '0.0',
            'speed' => '0.0',
            'datetime' => ''
        ]
    ];

    protected $viewLocationSet =[
        [
            '_id' => '300001',
            'organisation_id' => '10001',
            'vehicle_id' => '120001',
            'queued_at' => '2016-06-09T20:55:10+10:00',
            'sent_at' => '2016-06-09T20:56:10+10:00',
            'sid' => '2222222',
            'sid_response' => '9999999',
            'latitude' => -34.04387,
            'longitude' => 150.84342419999996,
            'course' => 0.00,
            'speed' => 0.5204,
            'datetime' => '2016-07-02T21:05:43+10:00',
            'status' => 'received'
        ],[
            '_id' => '300002',
            'organisation_id' => '10001',
            'vehicle_id' => '120001',
            'queued_at' => '2016-06-09T21:00:10+10:00',
            'sent_at' => '2016-06-09T21:01:10+10:00',
            'sid' => '2222222',
            'sid_response' => '9999999',
            'latitude' => -34.01387,
            'longitude' => 150.8434242,
            'course' => 275.00,
            'speed' => 40.3304,
            'datetime' => '2016-07-02T21:15:43+10:00',
            'status' => 'received'
    ]

    ];


    /**
     * @return \Zumba\PHPUnit\Extensions\Mongo\Client\Connector
     */
    public function getMongoConnection()
    {
        if (empty($this->connection)) {

            $this->connection = new Connector(
                new \MongoClient('mongodb://' . $this->db_username . ':' . $this->db_password . '@' . $this->db_host . ':' . $this->db_port));
            $this->connection->setDb($this->database);
        }
        return $this->connection;
    }


    /**
     * @return \Zumba\PHPUnit\Extensions\Mongo\DataSet\DataSet
     */
    public function getMongoDataSet()
    {

        if (empty($this->dataSet)) {
            $this->dataSet = new DataSet($this->getMongoConnection());
            $this->dataSet->setFixture($this->setMongoDates($this->getFixture()));
        }
        $this->twilioUser = null;
        return $this->dataSet;
    }

    abstract protected function getFixture();

    /**
     * @param Organisation $org
     * @return mixed
     */
    protected function twilioUser(Organisation $org = null){

        if (is_null($this->twilioUser)) {
            $this->twilioUser = $this->user($org);
            $this->twilioUser->username = bin2hex(random_bytes(16));
            $this->orgset[0]['twilio_user_password'] = bin2hex(random_bytes(16));
            $this->twilioUser->password = bcrypt($this->orgset[0]['twilio_user_password']);
            $this->twilioUser->save();
            try{
                if (is_null($org))
                    $org = $this->twilioUser->organisation;
                $this->twilioUser->twilioUserOrganisation()->save($org);
                $org->twilio_user_password = $this->orgset[0]['twilio_user_password'];
                $org->save();
            } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            }
         }
        return $this->twilioUser;
    }

    /**
     * @param Organisation $org
     * @return mixed
     */
    protected function firstUser(Organisation $org = null){

        $user = $this->user($org);
        if (is_null($org))
            $org = $user->organisation;
        try{
            $user->firstUserOrganisation()->save($org);
          } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
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
        $this->getMongoDataSet();
        $user = factory(User::class)->create();
        try{
            $uorg = $org?:Organisation::where('_id',$this->orgset[0]['_id'])->firstOrFail();
            $uorg->users()->save($user);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
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
                    ? $this->localDateTimeToMongoUTCDateTime($value)
                    : $this->iso8601LocalStringToMongoDate($value);
            }
        }
        return $fixture;
    }

    /**
     * @param $value
     * @return MongoDate
     */
    protected function iso8601LocalStringToMongoDate($value)
    {
        return new \MongoDate($this->iso8601LocalStringToMongoUTCDatetime($value));
    }

    protected function iso8601LocalStringToMongoUTCDatetime($value)
    {
        $dateTime = new \DateTime($value);
        return $this->localDateTimeToMongoUTCDateTime($dateTime);
    }


    /**
     * Assert that a given where condition exists in the database.
     *
     * @param  string  $table
     * @param  array  $data
     * @param  string  $connection
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
     * @param  string  $table
     * @param  array  $data
     * @param  string  $connection
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

}