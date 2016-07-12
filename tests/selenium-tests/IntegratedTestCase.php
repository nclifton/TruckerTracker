<?php
/**
 *
 * @version 0.0.1: IntegratedTestCase.php 14/06/2016T17:38
 * @author Clifton IT Foundries Pty Ltd
 * @link http://cliftonwebfoundry.com.au
 * @copyright Copyright (c) 2016 Clifton IT Foundries Pty Ltd. All rights Reserved
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 *
 **/

namespace TruckerTracker;

include_once __DIR__ . '/../TestTrait.php';

use Closure;
use Guzzle;
use Illuminate\Contracts\Console\Kernel;
use InvalidArgumentException;
use Laracasts\Integrated\Emulator;
use Laracasts\Integrated\JavaScriptAwareEmulator;
use Symfony\Component\Process\Process;
use WebDriver\Exception\ElementNotVisible;
use WebDriver\Exception\NoSuchElement;
use WebDriver\WebDriver;

abstract class IntegratedTestCase extends \Laracasts\Integrated\Extensions\Selenium implements Emulator, JavaScriptAwareEmulator
{

    use TestTrait;

    /**
     * The base URL to use while testing the application.
     *
     * @var string
     */
    protected $baseUrl = 'http://homestead.app';

    /**
     * @var \Illuminate\Foundation\Application
     */
    protected $app;
    protected $process;


    public function newSession()
    {
        $host = 'http://localhost:4444/wd/hub';

        $capabilities = [
            'browserName' => 'chrome',
                'args' => "\"user-data-dir=/Users/nclifton/Library/Application Support/Google/Chrome/Profile 3\""
            ];

        $this->webDriver = new WebDriver($host);

        return $this->session = $this->webDriver->session($this->getBrowserName(), $capabilities);

    }

    /**
     * Setup the test environment.
     *
     * @return void
     */
    public function setUp()
    {
        if (!$this->app) {
            $this->app = $this->createApplication();
        }

    }

    /**
     * Creates the application.
     *
     * @return \Illuminate\Foundation\Application
     */
    public function createApplication()
    {
        $app = require __DIR__ . '/../../bootstrap/app.php';

        $app->make(Kernel::class)->bootstrap();

        return $app;
    }

    /**
     * @param int $userKey
     * @return $this
     */
    protected function login($userKey = 0)
    {
        $this->visit('/');
        $this->click('Login')
            ->type($this->loginUserSet[$userKey]['email'], '#email')
            ->type($this->loginUserSet[$userKey]['password'], '#password')
            ->findByCssSelector('button.btn-primary')->click();
        $this->wait(2000);
        return $this;
    }


    /**
     * Filter according to an element's name or id attribute.
     *
     * @param  string $cssSelector
     * @param  string $element
     * @return \WebDriver\Element
     */
    protected function findByCssSelector($cssSelector, $element = '*')
    {
        try {
            return $this->session->element('css selector', "{$cssSelector}");
        } catch (NoSuchElement $e) {
            throw new InvalidArgumentException(
                "Couldn't find an element, '{$element}', using CSS selector '{$cssSelector}'."
            );
        }
    }

    /**
     * @param $cssSelector
     * @return \WebDriver\Element
     */
    protected function byCssSelector($cssSelector)
    {
        return $this->findByCssSelector($cssSelector);
    }

    /**
     * @param $id
     * @param string $element
     * @return \WebDriver\Element
     * @throws \Exception
     */
    protected function byId($id, $element = '*')
    {
        $id = str_replace('#', '', $id);
        try {
            return $this->session->element('css selector', "#{$id}");
        } catch (NoSuchElement $e) {
            throw new InvalidArgumentException(
                "Couldn't find an element, '{$element}', using id '{$id}'."
            );
        }
    }

    /**
     * @param $driver
     * @return $this
     */
    protected function addDriver($driver)
    {
        $this->clickOnElement('btn-add-driver');
        $this->wait(2000); // wait for animation

        $this->type($driver['first_name'], '#first_name');
        $this->type($driver['last_name'], '#last_name');
        $this->type($driver['mobile_phone_number'], '#driver_mobile_phone_number');
        $this->type($driver['drivers_licence_number'], '#drivers_licence_number');
        $this->clickOnElement('btn-save-driver');
        $this->wait(2000); // wait for animation
        return $this;
    }

    /**
     * @param $nameOrId
     * @return $this
     */
    protected function clickOnElement($nameOrId)
    {
        $crawler = $this->findByNameOrId($nameOrId);
        $crawler->click();
        return $this;
    }

    /**
     * @param Closure $closure
     * @param string $message
     */
    private function notSeeElement(Closure $closure, $message = 'Element exists')
    {
        try {
            $closure();
            $this->fail($message);
        } catch (\InvalidArgumentException $e) {
            $this->assertStringStartsWith('Couldn\'t find an element', $e->getMessage());
        }
    }

    /**
     * @param Closure $closure
     * @param string $message
     */
    private function seeElement(Closure $closure, $message = 'Element exists')
    {
        try {
            $closure();
        } catch (\InvalidArgumentException $e) {
            $this->fail($message);
        }
    }

    /**
     * @param $id
     * @param string $message
     */
    protected function seeById($id, $message = 'Element exists')
    {
        $this->seeElement(function () use ($id) {
            $this->byId($id);
        }, $message);
    }

    /**
     * @param $cssSelector
     * @param string $message
     */
    protected function seeByCssSelector($cssSelector, $message = 'Element exists')
    {
        $this->seeElement(function () use ($cssSelector) {
            $this->byCssSelector($cssSelector);
        }, $message);
    }

    /**
     * @param $xpath
     * @param string $message
     */
    protected function seeByXpath($xpath, $message = 'Element exists')
    {
        $this->seeElement(function () use ($xpath) {
            $this->byXPath($xpath);
        }, $message);
    }

    /**
     * @param $id
     * @param $message
     */
    protected function notSeeId($id, $message='Element not exists')
    {
        $this->notSeeElement(function () use ($id) {
            $this->byId($id);
        }, $message);
    }

    /**
     * @param $cssSelector
     * @param $message
     */
    protected function notSeeCssSelector($cssSelector, $message='Element not exists')
    {
        $this->notSeeElement(function () use ($cssSelector) {
            $this->byCssSelector($cssSelector);
        }, $message);
    }

    /**
     * @param $xpath
     * @param string $element
     * @return \WebDriver\Element
     * @throws \Exception
     */
    protected function byXPath($xpath, $element = '*')
    {
        try {
            return $this->session->element('xpath', "{$xpath}");
        } catch (NoSuchElement $e) {
            throw new InvalidArgumentException(
                "Couldn't find an element, '{$element}', using xpath '{$xpath}'."
            );
        }

    }

    /**
     * @param $text
     * @param $nameOrId
     * @return $this
     */
    protected function clearType($text, $nameOrId)
    {
        $this
            ->findByNameOrId($nameOrId)
            ->clear();
        $this
            ->type($text, $nameOrId);
        return $this;
    }

    /**
     * @param $element
     * @param string $message
     */
    protected function assertEnabled($element, $message = 'element enabled')
    {
        $this->assertThat($element
            ->attribute('disabled'),
            $this->logicalOr(
                $this->isNull(),
                $this->isFalse()),
            $message);
    }

    /**
     * @param $element
     * @param string $message
     */
    protected function assertDisabled($element, $message = 'element disabled')
    {
        $this->assertThat($element
            ->attribute('disabled'), $this
            ->logicalAnd($this
                ->logicalNot($this
                    ->isNull()), $this
                ->isTrue()),
            $message);
    }

    /**
     * Continuously poll the page, until you find an element
     * with the given name or id.
     *
     * @param  string  $element
     * @param  integer $timeout
     * @return static
     */
    public function waitForElement($element, $timeout = 5000)
    {
        $this->session->timeouts()->postImplicit_wait(['ms' => $timeout]);

        try {
            $this->findByNameOrIdVisible($element);
        } catch (InvalidArgumentException $e) {
            throw new InvalidArgumentException(
                "Hey, what's happening... Look, I waited {$timeout} milliseconds to see an element with " .
                "a name or id of '{$element}', but no luck. \nIf you could take a look, that'd be greaaattt..."
            );
        }

        return $this;
    }

    /**
     * Filter according to an element's name or id attribute.
     *
     * @param  string $name
     * @param  string $element
     * @return Crawler
     */
    protected function findByNameOrIdVisible($name, $element = '*')
    {
        $name = str_replace('#', '', $name);

        try {
            return $this->session->element('css selector', "#{$name}, *[name={$name}]")->displayed();
        } catch (NoSuchElement $e) {
            throw new InvalidArgumentException(
                "Couldn't find an element, '{$element}', with a name or class attribute of '{$name}'."
            );
        } catch (ElementNotVisible $e){
            throw new InvalidArgumentException(
                "Element not visible, '{$element}', with a name or class attribute of '{$name}'."
            );
        }
    }

    /**
     * @param $url
     * @param $twilioUser
     * @param $dbOrg
     * @param $sid
     * @param $status
     * @param $hasMobilePhoneNumber
     */
    protected function postStatusUpdate($twilioUser, $dbOrg, $sid, $status, $mobile_phone_number)
    {
        $url = http_build_url($this->baseUrl, [
            'path' => '/incoming/message/status'
        ]);
        $response = Guzzle::post($url, [
            'auth' => [$twilioUser['username'], $dbOrg['twilio_user_password']],
            'body' => [
                'MessageSid' => $sid,
                'AccountSid' => $dbOrg['twilio_account_sid'],
                'MessageStatus' => $status,
                'To' => $mobile_phone_number,
                'From' => $dbOrg['twilio_phone_number']
            ]
        ]);
        $this->assertEquals($response->getStatusCode(), 200, 'test sending status updatec to incoming comtroller');

    }

    /**
    * @param $twilioUser
    * @param $dbOrg
    * @param $org
    * @param $mobile_phone_number
    * @param $messageBody
    */
    protected function postMessageToIncomingController($twilioUser, $dbOrg, $org, $mobile_phone_number, $messageBody)
    {
        $url = http_build_url($this->baseUrl, [
            'path' => '/incoming/message'
        ]);
        $response = Guzzle::post($url, [
            'auth' => [$twilioUser['username'], $dbOrg['twilio_user_password']],
            'body' => [
                'MessageSid' => '9999999',
                'SmsSid' => '9999999',
                'AccountSid' => $org['twilio_account_sid'],
                'MessagingServiceSid' => 'MG123456789012345678901234',
                'From' => $mobile_phone_number,
                'To' => $org['twilio_phone_number'],
                'Body' => $messageBody,
                'NumMedia' => '0'
            ]
        ]);
        $this->assertEquals($response->getStatusCode(), 200, 'sending message to incoming controller');
    }
}
