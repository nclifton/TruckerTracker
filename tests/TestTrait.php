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
    protected $db_host = 'homestead.app';
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
    protected $conversationSet = [
        [
            '_id' => 'ffffffffff01',
            'organisation_id' => '10001',
            'driver_id' => '110001',
            'message_text' => 'hello',
            'queued_at' => '2016-06-09T20:45:10+10:00',
            'sent_at' => '2016-06-09T20:46:10+10:00',
            'delivered_at' => '2016-06-09T20:47:10+10:00',
            'status' => 'delivered',
            'sid' => '1111111'
        ], [
            '_id' => 'ffffffffff02',
            'organisation_id' => '10001',
            'driver_id' => '110001',
            'message_text' => 'Good morning',
            'received_at' => '2016-06-09T20:48:10+10:00',
            'status' => 'received',
            'sid' => '2222222'
        ], [
            '_id' => 'ffffffffff03',
            'organisation_id' => '10001',
            'driver_id' => '110001',
            'message_text' => 'busy day today. Please proceed to point A and collect box X and drop off to address Y',
            'queued_at' => '2016-06-09T20:49:10+10:00',
            'sent_at' => '2016-06-09T20:50:10+10:00',
            'delivered_at' => '2016-06-09T20:51:10+10:00',
            'status' => 'delivered',
            'sid' => '3333333'
        ], [
            '_id' => 'ffffffffff04',
            'organisation_id' => '10001',
            'driver_id' => '110001',
            'message_text' => 'TRAVELLING',
            'received_at' => '2016-06-09T20:55:10+10:00',
            'status' => 'received',
            'sid' => '4444444'
        ], [
            '_id' => 'ffffffffff05',
            'organisation_id' => '10001',
            'driver_id' => '110002',
            'message_text' => 'Having a break',
            'received_at' => '2016-06-09T21:30:10+10:00',
            'status' => 'received',
            'sid' => 'DDDDDDDDDD'
        ], [
            '_id' => 'ffffffffff06',
            'organisation_id' => '10001',
            'driver_id' => '110001',
            'message_text' => 'THERE',
            'received_at' => '2016-06-09T21:40:10+10:00',
            'status' => 'received',
            'sid' => '5555555'
        ], [
            '_id' => 'ffffffffff07',
            'organisation_id' => '10001',
            'driver_id' => '110001',
            'message_text' => "Great, now when you're done there please proceed to point B and collect box Y and drop off to address Z",
            'queued_at' => '2016-06-09T21:45:10+10:00',
            'sent_at' => '2016-06-09T21:46:10+10:00',
            'delivered_at' => '2016-06-09T21:47:10+10:00',
            'status' => 'delivered',
            'sid' => '6666666'
        ], [
            '_id' => 'ffffffffff08',
            'organisation_id' => '10001',
            'driver_id' => '110001',
            'message_text' => "?",
            'queued_at' => '2016-06-09T21:50:10+10:00',
            'sent_at' => '2016-06-09T21:51:10+10:00',
            'status' => 'sent',
            'sid' => '7777777'
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

    protected $orgSet = [
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

    protected $driverSet = [
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

    protected $vehicleSet = [
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
            'sid' => '2222222'
        ], [
            '_id' => '300002',
            'organisation_id' => '10001',
            'vehicle_id' => '120001',
            'queued_at' => '2016-06-09T20:55:10+10:00',
            'sent_at' => '2016-06-09T20:56:10+10:00',
            'delivered_at' => '2016-06-09T21:01:10+10:00',
            'status' => 'delivered',
            'sid' => '3333333'
        ]
    ];

    protected $viewLocationSetJson = '[
	{
		"_id": "577378400d82750b6e1ff331",
		"organisation_id": "10001",
		"vehicle_id": "120001",
		"queued_at": "2016-06-29 17:26:57",
		"status": "received",
		"sid": "SM12e90c98cd4f42a494950d28220ae13d",
		"sent_at": "2016-06-29 17:26:58",
		"delivered_at": "2016-06-29 17:27:07",
		"sid_response": "SM0e2ef503a738065c9f15cf2dbca3d43a",
		"latitude": -34.04402,
		"longitude": 150.84327,
		"course": 0,
		"speed": 0.037,
		"datetime": "2016-06-29 15:27:00",
		"received_at": "2016-06-29 17:27:12"
	},
	{
		"_id": "5773a58e0d82750c8d2dafa1",
		"organisation_id": "10001",
		"vehicle_id": "120001",
		"queued_at": "2016-06-29 20:40:15",
		"status": "received",
		"sid": "SM524040f723af41618cc7b51859949ce4",
		"sent_at": "2016-06-29 20:40:16",
		"delivered_at": "2016-06-29 20:40:26",
		"sid_response": "SM96d1dedbc8713c009ceca931e011afdb",
		"latitude": -34.08347,
		"longitude": 150.81274,
		"course": 123.15,
		"speed": 0,
		"datetime": "2016-06-29 18:40:00",
		"received_at": "2016-06-29 20:40:31"
	},
	{
		"_id": "57772ed60d827504676df431",
		"organisation_id": "10001",
		"vehicle_id": "120001",
		"queued_at": "2016-07-02 13:02:47",
		"status": "received",
		"sid": "SMca3c9a10f6174bc98b7e6e19e85ca958",
		"sent_at": "2016-07-02 13:02:48",
		"delivered_at": "2016-07-02 13:03:00",
		"sid_response": "SMf94a51bec9b7a40ab6b666e25f444aa1",
		"latitude": -34.04404,
		"longitude": 150.8433,
		"course": 244.94,
		"speed": 0.0074,
		"datetime": "2016-06-30 16:23:00",
		"received_at": "2016-07-02 13:03:04"
	},
	{
		"_id": "57772edb0d8275046657d4e1",
		"organisation_id": "10001",
		"vehicle_id": "120002",
		"queued_at": "2016-07-02 13:02:52",
		"status": "received",
		"sid": "SM2f3fc762f4514b3d947642c664912f89",
		"sent_at": "2016-07-02 13:02:53",
		"sid_response": "SMfd14ee7fae976d125c6c832634ed8c2e",
		"latitude": -34.07045,
		"longitude": 150.63681,
		"course": 8.97,
		"speed": 0.3556,
		"datetime": "2016-07-02 11:04:00",
		"received_at": "2016-07-02 13:04:31",
		"delivered_at": "2016-07-02 13:04:28"
	},
	{
		"_id": "5779121d0d8275046657d4e2",
		"organisation_id": "10001",
		"vehicle_id": "120001",
		"queued_at": "2016-07-03 23:24:46",
		"status": "received",
		"sid": "SMd873136b738647baa47ce88b0dd68536",
		"sent_at": "2016-07-03 23:24:47",
		"delivered_at": "2016-07-03 23:24:58",
		"sid_response": "SM6e9c01a806b1372130fa3ea739b64a67",
		"latitude": -34.04404,
		"longitude": 150.84322,
		"course": 299.23,
		"speed": 0.2334,
		"datetime": "2016-07-03 21:24:00",
		"received_at": "2016-07-03 23:25:04"
	},
	{
		"_id": "577a0d7a0d8275046657d4e4",
		"organisation_id": "10001",
		"vehicle_id": "120001",
		"queued_at": "2016-07-04 17:17:16",
		"status": "received",
		"sid": "SMda71ee93ee034c43b95b956fd469822f",
		"sent_at": "2016-07-04 17:17:17",
		"delivered_at": "2016-07-04 17:17:27",
		"sid_response": "SMa1f1c45478573178568482f44787391c",
		"latitude": -34.06582,
		"longitude": 150.84198,
		"course": 172.82,
		"speed": 0.0185,
		"datetime": "2016-07-04 15:17:00",
		"received_at": "2016-07-04 17:17:33"
	},
	{
		"_id": "577a17390d8275046657d4e5",
		"organisation_id": "10001",
		"vehicle_id": "120001",
		"queued_at": "2016-07-04 17:58:50",
		"status": "received",
		"sid": "SMf85318ba2db7436db38036e1d4ac26b3",
		"sent_at": "2016-07-04 17:58:51",
		"delivered_at": "2016-07-04 17:58:57",
		"sid_response": "SM308a4a94c8786dd84a77aa42c6c1def1",
		"latitude": -34.0625,
		"longitude": 150.84487,
		"course": 195.19,
		"speed": 40.1773,
		"datetime": "2016-07-04 15:58:00",
		"received_at": "2016-07-04 17:59:02"
	}
]';
        
  


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
            $this->dataSet
                ->setFixture($this
                    ->setIds($this
                        ->setMongoDates($this
                            ->getFixture())));
        }
        $this->twilioUser = null;
        return $this->dataSet;
    }

    protected function setIds($fixture){
        foreach ($fixture as $key => $value) {
            if (is_array($value)) {
                $fixture[$key] = $this->setIds($value);
            } else if ($key == '_id' && strlen($fixture[$key]) == 24 && ctype_xdigit($fixture[$key]) ) {
                $fixture[$key] = new \MongoId($fixture[$key]);
            }
        }
        return $fixture;
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
            $this->orgSet[0]['twilio_user_password'] = bin2hex(random_bytes(16));
            $this->twilioUser->password = bcrypt($this->orgSet[0]['twilio_user_password']);
            $this->twilioUser->save();
            try{
                if (is_null($org))
                    $org = $this->twilioUser->organisation;
                $this->twilioUser->twilioUserOrganisation()->save($org);
                $org->twilio_user_password = $this->orgSet[0]['twilio_user_password'];
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
            $uorg = $org?:Organisation::where('_id',$this->orgSet[0]['_id'])->firstOrFail();
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


}