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
        $queued_at = $dbLoc['queued_at']->toDateTime()->setTimeZone(new \DateTimeZone('Australia/Sydney'));

        $this->assertNotNull($id);
        $this->assertThat($this->byId('location'.$id)->displayed(),$this->isTrue());

        $this->assert_location_line($id, $vehicle, 'queued', $org, $queued_at);

        $dbOrg = $this->connection->collection('organisations')->findOne(['_id'=>$org['_id']]);
        $twilioUser = $this->connection->collection('users')->findOne(['_id'=>$dbOrg['twilio_user_id']]);

        $this->wait();

        $sent_at = new \DateTime();
        $this->postStatusUpdate($twilioUser,$dbOrg,$sid,'sent',$vehicle['mobile_phone_number']);

        $this->wait();

        $this->assert_location_line($id, $vehicle, 'sent', $org, $sent_at);

        $delivered_at = new \DateTime();
        $this->postStatusUpdate($twilioUser, $dbOrg, $sid, 'delivered', $vehicle['mobile_phone_number']);

        $this->wait();

        $this->assert_location_line($id, $vehicle, 'delivered', $org, $delivered_at);

        $received_at = new \DateTime();
        $this->postMessageToIncomingController(
            $twilioUser,
            $dbOrg,
            $org,
            $vehicle['mobile_phone_number'],
            'Lat:S34.04387,Lon:E150.84342419999996,Course:0.00,Speed:0.5204,DateTime:16 -07 -02 21:05:43');

        $this->wait();

        $this->assert_location_line($id, $vehicle, 'received', $org, $received_at);

        $this
            ->assertThat($this
                ->byCssSelector('#location'.$id.' .open-modal-location-view')
                ->displayed(),$this
                ->isTrue());
        
        $this->wait();
        
        $this->byCssSelector('#location'.$id.' button.delete-location')->click();
        $this->wait();
        
        $this->notSeeId('location'.$id);

    }

    /**
     * @param $id
     * @param $vehicle
     * @param $expected_status
     * @param $org
     * @param $delivered_at
     */
    protected function assert_location_line($id, $vehicle, $expected_status, $org, \Datetime $delivered_at)
    {
        $this
            ->assertThat($this
                ->byCssSelector('#location' . $id . ' span.registration_number')
                ->text(), $this
                ->equalTo($vehicle['registration_number']));
        $this
            ->assertThat($this
                ->byCssSelector('#location' . $id . ' span.status')
                ->text(), $this
                ->equalTo($expected_status));
        $actualDateString = $this
            ->byCssSelector('#location' . $id . ' span.status_at')
            ->text();
        $this
            ->assertThat(
                \DateTime::createFromFormat($org['datetime_format'], $actualDateString)
                    ->getTimestamp(), $this
                ->equalTo($delivered_at
                    ->getTimestamp(), 10),
                "actual: $actualDateString expected: ".$delivered_at->format($org['datetime_format']));
    }


}
