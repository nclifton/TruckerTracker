<?php

namespace TruckerTracker;

require_once __DIR__ . '/IntegratedTestCase.php';

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
            'messages' => $this->messageSet,
            'locations' => $this->locationSet
        ];
    }

    /**
     * @test
     */
    public function homePage()
    {

        // Arrange
        $message = $this->messageSet[0];
        $driver = $this->driverset[0];

        // Act
        $this->login();

        // Assert

        $results = $this->getMongoConnection()->collection('locations')->find(
            [
                'organisation_id' => $this->orgset[0]['_id'],
                'status' => 'sent'
            ]);
        $id = null;
        foreach ($results as $doc) {
            $id = $doc['_id'];
        }
        $this->assertNotNull($id);

        $this
            ->assertThat($this
                ->byId('#message' . $message['_id'])
                ->attribute('title'), $this
                ->equalTo($message['message_text']));
        $this
            ->assertThat($this
                ->byCssSelector('#message'.$message['_id'].' .first_name')
                ->text(),$this
                ->equalTo($driver['first_name']));
        $this
            ->assertThat($this
                ->byCssSelector('#message'.$message['_id'].' .last_name')
                ->text(),$this
                ->equalTo($driver['last_name']));
        $this
            ->assertThat($this
                ->byCssSelector('#message'.$message['_id'].' .status')
                ->text(),$this
                ->equalTo('sent'));
        $dateTime = new \DateTime($message['sent_at']);
        $dateTime->setTimezone(new \DateTimezone('Australia/Sydney'));
        $dateString = $dateTime->format($this->orgset[0]['datetime_format']);
        $this
            ->assertThat($this
                ->byCssSelector('#message'.$message['_id'].' .sent_at')
                ->text(),$this
                ->equalTo($dateString));

    }

}
