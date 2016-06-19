<?php

namespace TruckerTracker;

require_once __DIR__.'/IntegratedTestCase.php';

class HomePageAppearanceTest extends IntegratedTestCase
{
    
    protected function getFixture()
    {
         return [
            'users' => $this->fixtureUserset,
            'password_resets' => [],
            'organisations' => $this->orgset,
            'drivers' => $this->driverset,
            'vehicles' => $this->vehicleset,
            'messages' => $this->messageset,
            'locations' => $this->locationSet
        ];
    }

    /**
     * @test
     */
    public function homePage(){

        // Arrange

        // Act
        $this->login();

        // Assert

        $results = $this->getMongoConnection()->collection('locations')->find(
            [
                'organisation_id' => $this->orgset[0]['_id'],
                'status' => 'sent'
            ]);
        $id = null;
        foreach ($results as $doc){
            $id = $doc['_id'];
        }
        $this->assertNotNull($id);

        


    }

}
