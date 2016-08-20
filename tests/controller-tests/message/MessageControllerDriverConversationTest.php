<?php
/**
 *
 * @version 0.0.1: MessageControllerDriverConversationTest.php 6/07/2016T09:34
 * @author Clifton IT Foundries Pty Ltd
 * @link http://cliftonwebfoundry.com.au
 * @copyright Copyright (c) 2016 Clifton IT Foundries Pty Ltd. All rights Reserved
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 *
 **/


namespace TruckerTracker;
use Artisan;
use Faker\Provider\DateTime;

require_once __DIR__ . '/MessageControllerTestCase.php';


class MessageControllerDriverConversationTest extends MessageControllerTestCase
{


    protected $conversationSet;



    protected function artisanSeedDb()
    {
        Artisan::call('db:seed', ['--class' => 'MessageControllerDriverConversationTestDbSeeder']);
        $this->conversationSet = $this->conversationSet();

    }


    /**
     *
     * get an conversation - a set of messages with one or more drivers
     *
     * @test
     */
    public function gets_a_list_of_messages_for_two_drivers()
    {
        // Arrange
        $org = $this->orgSet[0];
        $user = $this->user();
        $driver1 = $this->driverSet[0];
        $driver2 = $this->driverSet[1];

        // Act
        $this->actingAs($user)->json('POST','/conversation',[$driver1['_id'],$driver2['_id']]);

        // Assert
        $this->assertResponseOk();
        $this->seeJsonEquals(
            [
                $this->message($this->conversationSet[0],$driver1),
                $this->message($this->conversationSet[1],$driver1),
                $this->message($this->conversationSet[2],$driver1),
                $this->message($this->conversationSet[3],$driver1),
                $this->message($this->conversationSet[4],$driver2),
                $this->message($this->conversationSet[5],$driver1),
                $this->message($this->conversationSet[6],$driver1),
                $this->message($this->conversationSet[7],$driver1)
            ]

        );

    }

    private function message($message, $driver)
    {
        $return = [
            '_id' => $message['_id'],
            'message_text' => $message['message_text'],
            'status' => $message['status'],
            'driver' => [
                '_id' => $driver['_id'],
                'first_name' => $driver['first_name'],
                'last_name' => $driver['last_name']
            ]
        ];
        foreach(array_keys($message) as $key){
            if(substr_compare($key, '_at', -3) === 0)
                $return[$key] = $message[$key];
        }
        return $return;
    }
}
