<?php

namespace WPNXMTest;

class Selenium2FirefoxTest extends \PHPUnit_Extensions_Selenium2TestCase
{
    public function prepareSession() {
        $res = parent::prepareSession();
        $this->url('/');
        $res->cookie()->remove('PHPUNIT_SELENIUM_TEST_ID');
        return $res;
    }

    protected function setUp()
    {
        $this->isSeleniumAvailable();

        $this->setupSpecificBrowser(array(
            'host' => '127.0.0.1',
            'port' => 4444,
            'browserName' => 'firefox',
            /*'desiredCapabilities' => array(
                array('chromeOptions' => array(
                    'args' => array('no-sandbox')
                ))
            ),*/
            'seleniumServerRequestsTimeout' => '50',
        ));

        $this->setBrowserUrl('http://127.0.0.1:80/');
    }

    public function isSeleniumAvailable()
    {
        $selenium_running = false;

        $fp = @fsockopen('localhost', 4444);
        if ($fp !== false) {
            $selenium_running = true;
            fclose($fp);
        }

        if ($selenium_running === false) {
             $this->markTestAsSkipped(
                'Selenium is not running on localhost:4444. Please start Selenium.'
             );
        }
    }

    public function testTitle()
    {
        $this->url('/');
        $this->assertEquals('WPИ-XM Server Stack for Windows - @APPVERSION@', $this->title());
    }

}
