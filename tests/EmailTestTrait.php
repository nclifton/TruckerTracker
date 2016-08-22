<?php
/**
 *
 * @version 0.0.1: EmailTestTrait.php 22/08/2016T14:43
 * @author Clifton IT Foundries Pty Ltd
 * @link http://cliftonwebfoundry.com.au
 * @copyright Copyright (c) 2016 Clifton IT Foundries Pty Ltd. All rights Reserved
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 *
 **/


namespace TruckerTracker;


use GuzzleHttp\Client;

trait EmailTestTrait
{

    /**
     * @var Client
     */
    private $mailcatcher;


    /**
     * @before
     */
    public function setupMailCatcher(){
        $this->mailcatcher = new Client(['base_url' => 'http://local.truckertracker.services:1080']);

        // clean emails between tests
        $this->cleanMessages();
    }

    // api calls
    public function cleanMessages()
    {
        $this->mailcatcher->delete('/messages');
    }

    public function getLastMessage()
    {
        $messages = $this->getMessages();
        if (empty($messages)) {
            $this->fail("No messages received");
        }
        // messages are in descending order
        return reset($messages);
    }
    public function getMessages()
    {
        $jsonResponse = $this->mailcatcher->get('/messages');
        return json_decode($jsonResponse->getBody());
    }

    public function assertEmailIsSent($description = '')
    {
        $this->assertNotEmpty($this->getMessages(), $description);
    }
}