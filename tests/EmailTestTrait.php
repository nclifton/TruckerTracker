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

    public function assertEmailSubjectContains($needle, $email, $description = '')
    {
        $this->assertContains($needle, $email->subject, $description);
    }

    public function assertEmailSubjectEquals($expected, $email, $description = '')
    {
        $this->assertContains($expected, $email->subject, $description);
    }

    public function assertEmailHtmlContains($needle, $email, $description = '')
    {
        $response = $this->getEmailHtml($email);
        $this->assertContains($needle, (string)$response->getBody(), $description);
    }

    public function assertEmailTextContains($needle, $email, $description = '')
    {
        $response = $this->mailcatcher->get("/messages/{$email->id}.plain");
        $this->assertContains($needle, (string)$response->getBody(), $description);
    }

    public function assertEmailSenderEquals($expected, $email, $description = '')
    {
        $response = $this->mailcatcher->get("/messages/{$email->id}.json");
        $email = json_decode($response->getBody());
        $this->assertEquals($expected, $email->sender, $description);
    }

    public function assertEmailRecipientsContain($needle, $email, $description = '')
    {
        $response = $this->mailcatcher->get("/messages/{$email->id}.json");
        $email = json_decode($response->getBody());
        $this->assertContains($needle, $email->recipients, $description);
    }

    /**
     * @param $email
     * @return \GuzzleHttp\Message\FutureResponse|\GuzzleHttp\Message\ResponseInterface|\GuzzleHttp\Ring\Future\FutureInterface|null
     */
    private function getEmailHtml($email)
    {
        $response = $this->mailcatcher->get("/messages/{$email->id}.html");
        return $response;
    }

    /**
     * @param $email
     * @return array
     */
    protected function getLinksInEmailHtml($email)
    {
        $doc = new \DOMDocument();
        libxml_use_internal_errors(true);
        $doc->loadHTML($this->getEmailHtml($email));
        $links = [];
        $arr = $doc->getElementsByTagName("a"); // DOMNodeList Object
        foreach ($arr as $item) { // DOMElement Object
            $href = $item->getAttribute("href");
            $text = trim(preg_replace("/[\r\n]+/", " ", $item->nodeValue));
            $links[] = [
                'href' => $href,
                'text' => $text
            ];
        }
        return $links;
    }

}