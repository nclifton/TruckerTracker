<?php
namespace TruckerTracker;

use Illuminate\Support\Facades\Redis;
use TruckerTracker\Http\Controllers\TwilioIncomingController;

Require_once __DIR__.'/IntegratedTestCase.php';


class LocationTest extends \TruckerTracker\IntegratedTestCase
{

    protected function getFixture()
    {
         return [
            'users' => $this->fixtureUserset,
            'password_resets' => [],
            'organisations' => $this->orgset,
            'drivers' => [],
            'vehicles' => $this->vehicleset,
            'messages' => [],
            'locations' => []
        ];
    }

    /**
     * @test
     */
    public function queues_Location_Request_gets_updated_and_deletes(){

        // Arrange
        $org = $this->orgset[0];
        $vehicle = $this->vehicleset[0];
        $_SERVER['SERVER_NAME'] = 'localhost';

        // Act
        $this->login();
        $this->byCssSelector('#vehicle'. $vehicle['_id'].' button.open-modal-location')->click();
        $this->wait(4000);
        $this->byId('btn-save-location')->click();
        $this->wait(10000);

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
        $dt = $dbLoc['queued_at']->toDateTime();
        $dt->setTimeZone(new \DateTimeZone('Australia/Sydney'));
        $queued_at = $dt->format($org['datetime_format']);

        $this->assertNotNull($id);
        $this->assertThat($this->byId('location'.$id)->displayed(),$this->isTrue());
        $this
            ->assertThat($this
                ->byCssSelector('#location'.$id.' span.registration_number')
                ->text(), $this
                ->equalTo($vehicle['registration_number']));
        $this
            ->assertThat($this
                ->byCssSelector('#location'.$id.' span.status')
                ->text(), $this
                ->equalTo('queued'));
        $this
            ->assertThat($this
                ->byCssSelector('#location'.$id.' span.sent_at')
                ->text(), $this
                ->equalTo($queued_at));


        $message = [
            "_id"=>$id,
            "queued_at"=>$queued_at,
            "sent_at"=>"08:56:10 PM Thu 09/06/16",
            "status"=>"received",
            "sid"=>"2222222",
            "latitude"=>-34.04387,
            "longitude"=>150.8434242,
            "course"=>0,
            "speed"=>0.5204,
            "datetime"=>"09:05:43 PM Sat 02/07/16",
            "sid_response"=>"9999999",
            "received_at"=>"2016-06-28 15:14:37",
            "vehicle"=>[
                "_id"=>"120001",
                "registration_number"=>"DD6664",
                "mobile_phone_number"=>"+61417673377",
                "tracker_imei_number"=>"355054/06/051610/4"
                ]
            ];
        Redis::publish('trucker-tracker.'.$org['_id'], json_encode($message));

        $this->wait(10000);

        $this
            ->assertThat($this
                ->byCssSelector('#location'.$id.' span.registration_number')
                ->text(), $this
                ->equalTo($vehicle['registration_number']));
        $this
            ->assertThat($this
                ->byCssSelector('#location'.$id.' span.status')
                ->text(), $this
                ->equalTo('sent'));
        $this
            ->assertThat($this
                ->byCssSelector('#location'.$id.' span.sent_at')
                ->text(), $this
                ->equalTo($message['sent_at']));

        $this->wait(10000);

        $this->byCssSelector('#location'.$id.' button.delete-location')->click();
        $this->wait(4000);
        
        $this->notSeeId('location'.$id);

    }

}
