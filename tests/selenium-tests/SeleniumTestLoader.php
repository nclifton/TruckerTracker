<?php
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../TruckerTrackerTestTrait.php';

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Events\Dispatcher;
use Illuminate\Container\Container;
use \Zumba\PHPUnit\Extensions\Mongo\Client\Connector;


abstract class SeleniumTestLoader extends \PHPUnit_Extensions_Selenium2TestCase
{
    use TruckerTrackerTestTrait;
    
    const BASE_URI = 'http://localhost:8000';
    protected $baseUrl = self::BASE_URI;
    protected $env = 'local';


    /**
     * @var \Zumba\PHPUnit\Extensions\Mongo\Client\Connector
     */
    protected $connection;
    /**
     * @var \Zumba\PHPUnit\Extensions\Mongo\DataSet\DataSet
     */
    protected $dataSet;

    protected $loginUserSet = [
        [
            'username' => 'test user 1',
            'emailAddress' => 'test1@cliftonwebfoundry.com.au',
            'password' => 'mstgpwd1'
        ], [
            'username' => 'test user2',
            'emailAddress' => 'test2@cliftonwebfoundry.com.au',
            'password' => 'mstgpwd1'
        ]
    ];

    /**
     * @before
     **/
    protected function setUp()
    {
        //parent::setUp();

        error_reporting(0);
        if ($this->env == 'local') {
            //$this->setBrowser('phantomjs');
            //$this->setBrowser('chrome');
            $this->setBrowser('firefox');
            $this->setBrowserUrl($this->baseUrl);
            $this->run_selenium_server();
            //$this->run_phantom_js();
        }
//THIS ELSE IS TO USE TRAVIS AND SOUCE LABS TO RUN THE TEST FOR CONTINOUS INTEGRATION
//IF YOU DONT USE IT, JUST DELETE IT
        else {
            $this->setPort(80);
            $user = 'YOUR USERNAME';
            $token = 'YOUR TOKEN';
            $this->setHost("$user:$token@ondemand.saucelabs.com");
            $this->setPort(80);
            $this->setBrowser('firefox');
            $this->setBrowserUrl($this->baseUrl);
        }

    }


    protected function run_selenium_server()
    {
        if ($this->selenium_server_already_running()) {
            fwrite(STDOUT, "Selenium server already running\n");
        } else {
            $selenium_jar = __DIR__ . "/../bin/selenium-server-standalone-2.53.0.jar";
            $chromeWebDriver = __DIR__ . "/../bin/chromedriver";
            $firefoxprofile = 'test-debug';
            exec("java -jar {$selenium_jar} " .
                //"-Dwebdriver.chrome.driver={$chromeWebDriver} " .
                "-Dwebdriver.firefox.profile={$firefoxprofile} >/dev/null 2>/dev/null &");

            sleep(3);
        }
    }

    protected function run_phantom_js()
    {
        if ($this->phantom_js_already_running()) {
            fwrite(STDOUT, "PhantomJS already running\n");
        } else {
            fwrite(STDOUT, "Starting PhantomJS\n");
            exec("phantomjs --webdriver=8080 --webdriver-selenium-grid-hub=http://127.0.0.1:4444 &");
        }
    }

    protected function selenium_server_already_running()
    {
        return fsockopen("localhost", 4444);
    }

    protected function phantom_js_already_running()
    {
        try {
            return fsockopen("localhost", 8080);
        } catch (Exception $e) {
        }
    }

    public function setUpPage()
    {
        $this->currentWindow()->maximize();
    }

    protected function takeScreenShot($location){
        $fp = fopen($location,'wb');
        fwrite($fp,$this->currentScreenshot());
        fclose($fp);
    }

    protected function see($name)
    {
        return $this->byXpath("//*[contains(text(),'".$name."')]");
    }

    protected function seePageIs($name)
    {
        $this->assertEquals($this->baseUrl.$name, $this->url());
    }

    protected function clickLink($link)
    {
        $element = $this->byXpath("//a[@href='".$link."']");
        $element->click();
    }

    protected function visit($path)
    {
        $this->url($path);
        return $this;
    }

    protected function clickCustomSelectWith($attribute,$name,$value)
    {
        $script =  '$(\'select['.$attribute.'="'.$name.'"]\').next().find(\'ul\').find(\'input:radio[value="'.$value.'"]\').trigger(\'click\');';

        $this->execute(array(
            'script' => $script,
            'args'   => array()
        ));
    }

    protected function type($string,$selector){
        $this->byCssSelector($selector)->click();
        $this->keys($string);
        return $this;
    }


    protected function clearType($string,$selector){
        $this->byCssSelector($selector)->clear();
        $this->byCssSelector($selector)->click();
        $this->keys($string);
        return $this;
    }

    protected function sleep($seconds){
        sleep($seconds);
        return $this;
    }
    protected function waitForDisplayed($byMethod,$value,$timeout=3000){
        $this->waitUntil(function() use ($byMethod,$value){
            return $this->$byMethod($value)->displayed;
        }, $timeout);
        return $this;
    }
    protected function waitForNotDisplayed($byMethod,$value,$timeout=3000){
        $this->waitUntil(function() use ($byMethod,$value){
            return !$this->$byMethod($value)->displayed;
        }, $timeout);
        return $this;
    }

    protected function clearInput($cssSelector)
    {
        $this->byCssSelector($cssSelector)->clear();
        return $this;
    }

    protected function login($userKey = 0)
    {
        $this->visit('/');
        $this->clickLink($this->baseUrl.'/login');
        $this->type($this->loginUserSet[$userKey]['emailAddress'], '#email');
        $this->type($this->loginUserSet[$userKey]['password'], '#password');
        $this->byCssSelector('button.btn.btn-primary[type="submit"]')->click();
        sleep(5);
        return $this;
    }

    protected function addDriver($driver)
    {
        $this->clickOnElement('btn-add-driver');
        sleep(2); // wait for animation

        $this->clearType($driver['first_name'], '#first_name');
        $this->clearType($driver['last_name'], '#last_name');
        $this->clearType($driver['mobile_phone_number'], '#driver_mobile_phone_number');
        $this->clearType($driver['drivers_licence_number'], '#drivers_licence_number');
        $this->clickOnElement('btn-save-driver');
        sleep(2); // wait for animation
        return $this;
    }


    protected function notSeeElement(Closure $closure, $message = 'Element exists')
    {
        try {
            $closure();
            $this->fail($message);
        } catch (PHPUnit_Extensions_Selenium2TestCase_WebDriverException $e) {
            $this->assertEquals(PHPUnit_Extensions_Selenium2TestCase_WebDriverException::NoSuchElement, $e->getCode());
        }
    }


}