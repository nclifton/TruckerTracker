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

class TwilioControllerTestCase extends TestCase
{
    use TestTrait;
    protected $user;


    /**
     * @before
     */
    public function setUp()
    {
        parent::setUp();
        $this->user = $this->user();
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
            'organisations' => $this->orgset,
            'drivers' => $this->driverset,
            'vehicles' => $this->vehicleset,
            'messages' => [],
            'locations' => []
        ];
    }

    /**
     * @param $org
     * @param $to
     * @param $twilioUsername
     * @param $message_text
     * @param $expectedStatus
     * @return \Mockery\MockInterface
     * @internal param $driver
     */
    protected function mockTwilio($org, $to, $twilioUsername, $message_text, $expectedStatus)
    {
        $mockTwilioService = \Mockery::mock(Services_Twilio::class);
        $mockTwilio = \Mockery::mock(\TruckerTracker\Twilio\TwilioInterface::class);
        $mockTwilio->shouldReceive('setSid')->with($org['twilio_account_sid'])->once();
        $mockTwilio->shouldReceive('setToken')->with($org['twilio_auth_token'])->once();
        $mockTwilio->shouldReceive('setFrom')->with($org['twilio_phone_number'])->once();
        $mockTwilio->shouldReceive('getTwilio')->andReturn($mockTwilioService)->once();
        $mockTwilioService->account = $mockTwilioService;
        $mockServicesTwilioRestMessages = \Mockery::mock(Services_Twilio_Rest_Messages::class);
        $mockTwilioService->account->messages = $mockServicesTwilioRestMessages;
        $mockServicesTwilioRestMessage = \Mockery::mock(Services_Twilio_Rest_Message::class);
        $mockServicesTwilioRestMessage->status = $expectedStatus;
        $mockServicesTwilioRestMessage->sid = $this->messageset[0]['sid'];
        $mockServicesTwilioRestMessage->account_sid = $org['twilio_account_sid'];
        $creds = rawurlencode($twilioUsername) . ':' . rawurldecode($org['twilio_user_password']);
        $mockServicesTwilioRestMessages->shouldReceive('create')->once()->with(
            [
                'To' => $to,
                'From' => $org['twilio_phone_number'],
                'Body' => $message_text,
                'StatusCallback' => "http://${creds}@mcsweeneytg.com.au:8000/incoming/message/status"
            ])->andReturn($mockServicesTwilioRestMessage);
        return $mockTwilio;
    }

    protected function mockTwilioNeverUsed()
    {
        $mockTwilio = \Mockery::mock(\TruckerTracker\Twilio\TwilioInterface::class);
        $mockTwilio->shouldReceive('setSid')->never();
        return $mockTwilio;
    }

    /**
     * @param $org
     * @param $to
     * @param $expectedMessageText
     * @param $expectedStatus
     */
    protected function injectMockTwilio($org, $to, $twilioUsername, $expectedMessageText, $expectedStatus)
    {
        $this->app->instance(\TruckerTracker\Twilio\TwilioInterface::class,
            $this->mockTwilio($org, $to, $twilioUsername, $expectedMessageText, $expectedStatus));
    }

    protected function injectMockNeverUsedTwilio()
    {
        $this->app->instance(\TruckerTracker\Twilio\TwilioInterface::class,
            $this->mockTwilioNeverUsed());
    }
}