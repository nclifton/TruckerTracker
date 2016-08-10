<?php

/**
 *
 * @version 0.0.1: ${FILE_NAME} 5/06/2016T14:19
 * @author Clifton IT Foundries Pty Ltd
 * @link http://cliftonwebfoundry.com.au
 * @copyright Copyright (c) 2016 Clifton IT Foundries Pty Ltd. All rights Reserved
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 *
 **/
namespace TruckerTracker;

require_once __DIR__ . '/../../TestTrait.php';

use Config;
use \Mockery as m;
use TruckerTracker\Twilio\TwilioInterface;
use Twilio;

class TwilioControllerTestCase extends TestCase
{
    use TestTrait;
    protected $user;
    protected $subset;


    /**
     * @before
     */
    public function setUp()
    {
        parent::setUp();
        $this->user = $this->user();
        Config::set('url','http://local.truckertracker.services');
    }

    protected $twilio_cwf = [
        'twilio_account_sid' => 'AC44ff302474a347b508260e099573d042',
        'twilio_auth_token' => '01afd52146771821a2a4b4fc864230ab',
        'twilio_phone_number' => '+61481072148'
    ];

    protected function getFixture()
    {

        //$this->orgset[0] = array_merge($this->orgset[0],$this->twilio_cwf);

        return [
            'users' => [],
            'password_resets' => [],
            'organisations' => $this->orgSet,
            'drivers' => $this->driverSet,
            'vehicles' => $this->vehicleSet,
            'messages' => [],
            'locations' => []
        ];
    }

    /**
     * @after
     */
    public function tearDown()
    {
        \Mockery::close();
    }

    /**
     * @param $org
     * @param $to
     * @param $twilioUsername
     * @param $message_text
     * @param $expectedStatus
     */
    protected function mockTwilio($org, $to, $twilioUsername, $message_text, $expectedStatus)
    {
        $mockTwilioService = m::mock(\Services_Twilio::class);

        Twilio::shouldReceive('setSid')->with($org['twilio_account_sid'])->once();
        Twilio::shouldReceive('setToken')->with($org['twilio_auth_token'])->once();
        Twilio::shouldReceive('setFrom')->with($org['twilio_phone_number'])->once();
        Twilio::shouldReceive('getTwilio')->andReturn($mockTwilioService)->once();

        $mockTwilioService->account = $mockTwilioService;

        $mockServicesTwilioRestMessages = m::mock(\Services_Twilio_Rest_Messages::class);

        $mockTwilioService->account->messages = $mockServicesTwilioRestMessages;

        $mockServicesTwilioRestMessage = m::mock(\Services_Twilio_Rest_Message::class);

        $mockServicesTwilioRestMessage->status = $expectedStatus;
        $mockServicesTwilioRestMessage->sid = $this->messageSet[0]['sid'];
        $mockServicesTwilioRestMessage->account_sid = $org['twilio_account_sid'];

        $parts = [
          'scheme' => config('app.external_scheme','http'),
            'host' => config('app.external_host','external-host.com'),
            'port' => config('app.external_port'),
            'user' => $twilioUsername,
            'pass' => $org['twilio_user_password'],
            'path' => '/incoming/message/status'
        ];

        $statusCallBack = http_build_url('',$parts);

        $this->subset = m::subset(
            [
                'To' => $to,
                'From' => $org['twilio_phone_number'],
                'Body' => $message_text,
                'StatusCallback' => $statusCallBack
            ]);

        $mockServicesTwilioRestMessages
            ->shouldReceive('create')
            ->with($this->subset)->once()
            ->andReturn($mockServicesTwilioRestMessage);
    }

    protected function mockTwilioNeverUsed()
    {
        $mockTwilio = m::mock(TwilioInterface::class);
        Twilio::shouldReceive('setSid')->never();
        return $mockTwilio;
    }

    protected function injectMockNeverUsedTwilio()
    {
        $this->app->instance(TwilioInterface::class,
            $this->mockTwilioNeverUsed());
    }
}