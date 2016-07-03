<?php
namespace TruckerTracker;

use DB;
use Faker\Provider\DateTime;
use Guzzle;
use Illuminate\Support\Facades\Redis;
use Services_Twilio_Twiml;
use TruckerTracker\Http\Controllers\TwilioIncomingController;

Require_once __DIR__.'/IntegratedTestCase.php';


class LocationTest extends \TruckerTracker\IntegratedTestCase
{

    protected function getFixture()
    {
         return [
            'users' => $this->fixtureUserset,
            'password_resets' => [],
            'organisations' => $this->orgSet,
            'drivers' => [],
            'vehicles' => $this->vehicleSet,
            'messages' => [],
            'locations' => []
        ];
    }

    /**
     * @test
     */
    public function queues_Location_Request_gets_updated_and_deletes(){

        // Arrange
        $org = $this->orgSet[0];
        $vehicle = $this->vehicleSet[0];
        $_SERVER['SERVER_NAME'] = 'localhost';

        // Act
        $this->login();
        $this->byCssSelector('#vehicle'. $vehicle['_id'].' button.open-modal-location')->click();
        $this->wait(4000);
        $this->byId('btn-save-location')->click();
        $this->wait(6000);

        // Assert
        $count = 10;
        $dbLoc = null;
        while ($count > 0 && !($dbLoc)){
            --$count;
            $dbLoc = $this->getMongoConnection()->collection('locations')->findOne(
                [
                    'vehicle_id' => $vehicle['_id'],
                    'organisation_id' => $org['_id'],
                    'status' => 'queued'
                ]);
            $this->wait();
        }
        $this->assertNotNull($dbLoc);
        $id = $dbLoc['_id'];
        $sid = $dbLoc['sid'];
        $dt = $dbLoc['queued_at']->toDateTime();
        $dt->setTimeZone(new \DateTimeZone('Australia/Sydney'));
        $queued_at = $dt->format($org['datetime_format']);

        $this->assertNotNull($id);
        $this->assertThat($this->byId('location'.$id)->displayed(),$this->isTrue());
        $this
            ->assertThat($this
                ->byCssSelector('#location'.$id.' span.description')
                ->text(), $this
                ->equalTo($vehicle['registration_number'].' queued '.$queued_at));

        $dbOrg = $this->connection->collection('organisations')->findOne(['_id'=>$org['_id']]);
        $twilioUser = $this->connection->collection('users')->findOne(['_id'=>$dbOrg['twilio_user_id']]);

        $url = http_build_url($this->baseUrl,[
            'path'=>'/incoming/message/status'
        ]);

        $sent_at = (new \DateTime())->format($org['datetime_format']);
        $response = Guzzle::post($url,[
            'auth'=>[$twilioUser['username'],$dbOrg['twilio_user_password']],
            'body' => [
                'MessageSid' => $sid,
                'AccountSid' => $dbOrg['twilio_account_sid'],
                'MessageStatus' => 'sent',
                'To' => $vehicle['mobile_phone_number'],
                'From' => $dbOrg['twilio_phone_number']
            ]
        ]);

        $this->assertEquals($response->getStatusCode(),200,'test sending status updatec to incoming comtroller');

        $this->wait(10000);

        $this
            ->assertThat($this
                ->byCssSelector('#location' . $id . ' span.description')
                ->text(), $this
                ->stringStartsWith($vehicle['registration_number'].' sent'));
        $actualDateString = substr($this
            ->byCssSelector('#location' . $id . ' span.description')
            ->text(), 12);
        $actualDate = \DateTime::createFromFormat($org['datetime_format'],
            $actualDateString)->getTimestamp();
        $at = \DateTime::createFromFormat($org['datetime_format'],$sent_at)->getTimestamp();
        $this
            ->assertThat($actualDate, $this
                ->equalTo($at,5),'actual: $actualDateString expected: $sent_at');

        $this->wait(6000);

        $delivered_at = (new \DateTime())->format($org['datetime_format']);
        Guzzle::post($url,[
            'auth'=>[$twilioUser['username'],$dbOrg['twilio_user_password']],
            'body' => [
                'MessageSid' => $sid,
                'AccountSid' => $dbOrg['twilio_account_sid'],
                'MessageStatus' => 'delivered',
                'To' => $vehicle['mobile_phone_number'],
                'From' => $dbOrg['twilio_phone_number']
            ]
        ]);

        $this->wait(6000);

        $prefix = $vehicle['registration_number'] . ' delivered ';
        $this
            ->assertThat($this
                ->byCssSelector('#location' . $id . ' span.description')
                ->text(), $this
                ->stringStartsWith($prefix));
        $actualDateString = substr($this
            ->byCssSelector('#location' . $id . ' span.description')
            ->text(), strlen($prefix));
        $actualDate = \DateTime::createFromFormat($org['datetime_format'],
            $actualDateString)->getTimestamp();
        $at = \DateTime::createFromFormat($org['datetime_format'],$delivered_at)->getTimestamp();
        $this
            ->assertThat($actualDate, $this
                ->equalTo($at,5),"actual: $actualDateString expected: $delivered_at");

        $this->wait(6000);

        $url = http_build_url($this->baseUrl,[
            'path'=>'/incoming/message'
        ]);
        $received_at = (new \DateTime())->format($org['datetime_format']);
        Guzzle::post($url,[
            'auth'=>[$twilioUser['username'],$dbOrg['twilio_user_password']],
            'body' => [
                'MessageSid' => '9999999',
                'SmsSid' => '9999999',
                'AccountSid' => $org['twilio_account_sid'],
                'MessagingServiceSid' => 'MG123456789012345678901234',
                'From' => $vehicle['mobile_phone_number'],
                'To' => $org['twilio_phone_number'],
                'Body' => 'Lat:S34.04387,Lon:E150.84342419999996,Course:0.00,Speed:0.5204,DateTime:16 -07 -02 21:05:43',
                'NumMedia' => '0'
            ]
        ]);

        $this->wait(6000);

        $prefix = $vehicle['registration_number'] . ' received ';
        $this
            ->assertThat($this
                ->byCssSelector('#location' . $id . ' span.description')
                ->text(), $this
                ->stringStartsWith($prefix));
        $actualDateString = substr($this
            ->byCssSelector('#location' . $id . ' span.description')
            ->text(), strlen($prefix));
        $actualDate = \DateTime::createFromFormat($org['datetime_format'],
            $actualDateString)->getTimestamp();
        $at = \DateTime::createFromFormat($org['datetime_format'],$received_at)->getTimestamp();
        $this
            ->assertThat($actualDate, $this
                ->equalTo($at,5),"actual: $actualDateString expected: $received_at");

        $this
            ->assertThat($this
                ->byCssSelector('#location'.$id.' .open-modal-location-view')
                ->displayed(),$this
                ->isTrue());
        
        $this->wait(6000);
        
        $this->byCssSelector('#location'.$id.' button.delete-location')->click();
        $this->wait();
        
        $this->notSeeId('location'.$id);

    }

}
