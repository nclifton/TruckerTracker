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

    protected $conversationSet = [
        [
            '_id' =>                'ffffffffff01',
            'organisation_id' =>    '10001',
            'driver_id' =>          '110001',
            'message_text' =>       'hello',
            'queued_at' =>          '2016-06-09T20:45:10+10:00',
            'sent_at' =>            '2016-06-09T20:46:10+10:00',
            'delivered_at' =>       '2016-06-09T20:47:10+10:00',
            'status' =>             'delivered',
            'sid' =>                '1111111'
        ],[
            '_id' =>                'ffffffffff02',
            'organisation_id' =>    '10001',
            'driver_id' =>          '110001',
            'message_text' =>       'Good morning',
            'received_at' =>        '2016-06-09T20:48:10+10:00',
            'status' =>             'received',
            'sid' =>                '2222222'
        ],        [
            '_id' =>                'ffffffffff03',
            'organisation_id' =>    '10001',
            'driver_id' =>          '110001',
            'message_text' =>       'busy day today. Please proceed to point A and collect box X and drop off to address Y',
            'queued_at' =>          '2016-06-09T20:49:10+10:00',
            'sent_at' =>            '2016-06-09T20:50:10+10:00',
            'delivered_at' =>       '2016-06-09T20:51:10+10:00',
            'status' =>             'delivered',
            'sid' =>                '3333333'
        ],[
            '_id' =>                'ffffffffff04',
            'organisation_id' =>    '10001',
            'driver_id' =>          '110001',
            'message_text' =>       'TRAVELLING',
            'received_at' =>        '2016-06-09T20:55:10+10:00',
            'status' =>             'received',
            'sid' =>                '4444444'
        ],[
            '_id' =>                'ffffffffff05',
            'organisation_id' =>    '10001',
            'driver_id' =>          '110002',
            'message_text' =>       'Having a break',
            'received_at' =>        '2016-06-09T21:30:10+10:00',
            'status' =>             'received',
            'sid' =>                'DDDDDDDDDD'
        ],[
            '_id' =>                'ffffffffff06',
            'organisation_id' =>    '10001',
            'driver_id' =>          '110001',
            'message_text' =>       'THERE',
            'received_at' =>        '2016-06-09T21:40:10+10:00',
            'status' =>             'received',
            'sid' =>                '5555555'
        ],[
            '_id' =>                'ffffffffff07',
            'organisation_id' =>    '10001',
            'driver_id' =>          '110001',
            'message_text' =>       "Great, now when you're done there please proceed to point B and collect box Y and drop off to address Z",
            'queued_at' =>          '2016-06-09T21:45:10+10:00',
            'sent_at' =>            '2016-06-09T21:46:10+10:00',
            'delivered_at' =>       '2016-06-09T21:47:10+10:00',
            'status' =>             'delivered',
            'sid' =>                '6666666'
        ],[
            '_id' =>                'ffffffffff08',
            'organisation_id' =>    '10001',
            'driver_id' =>          '110001',
            'message_text' =>       "?",
            'queued_at' =>          '2016-06-09T21:50:10+10:00',
            'sent_at' =>            '2016-06-09T21:51:10+10:00',
            'status' =>             'sent',
            'sid' =>                '7777777'
        ]


    ];



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
        $response = $this->decodeResponseJson();
        $this->assertCount(6,$response);
        $this->seeJsonContains([
            'message_text' => $this->conversationSet[0]['message_text'],
            'driver' => [
                '_id' => $driver['_id'],
                'first_name' => $driver['first_name'],
                'last_name' => $driver['last_name'],
                'mobile_phone_number' => $driver['mobile_phone_number'],
                'drivers_licence_number' => $driver['drivers_licence_number']
            ]
        ]);
        foreach ($this->conversationSet as $key => $message){
            if (! in_array($key,[4,7]))
                $this->seeJsonContains([
                    'message_text' => $message['message_text'],
                ]);
        }

    }


}
