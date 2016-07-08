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
require_once __DIR__ . '/MessageControllerTestCase.php';


class MessageControllerDriverConversationTest extends MessageControllerTestCase
{


    protected function getFixture()
    {

        return [
            'users' =>              [],
            'password_resets' =>    [],
            'organisations' =>      $this->orgSet,
            'drivers' =>            $this->driverSet,
            'vehicles' =>           [],
            'messages' =>           $this->conversationSet,
            'locations' =>          []
        ];
    }

    /**
     *
     * get an conversation - a set of messages with one driver
     *
     * @test
     */
    public function gets_a_list_of_messages_for_one_driver()
    {
        // Arrange
        $org = $this->orgSet[0];
        $user = $this->user();
        $driver = $this->driverSet[0];

        // Act
        $this->actingAs($user)->get('/driver/'.$driver['_id'].'/conversation');

        // Assert
        $this->assertResponseOk();
        $this->seeJsonContains([
            '_id' => $driver['_id'],
            'first_name' => $driver['first_name'],
            'last_name' => $driver['last_name'],
            'mobile_phone_number' => $driver['mobile_phone_number'],
            'drivers_licence_number' => $driver['drivers_licence_number']
            ]);
        foreach ($this->conversationSet as $key => $message){
            if (! in_array($key,[4,7]))
                $this->seeJsonContains([
                    'message_text' => $message['message_text'],
                    'status' => $message['status']
                ]);
        }

    }


}
