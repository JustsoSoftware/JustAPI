<?php
/**
 * Definition of class BootstrapTest
 * 
 * @copyright  2014-today Justso GmbH
 * @author     j.schirrmacher@justso.de
 * @package    justso\justapi\test
 */

namespace justso\justapi\test;

use justso\justapi\Bootstrap;

/**
 * Class BootstrapTest
 *
 * @package justso\justapi\test
 */
class BootstrapTest extends \PHPUnit_Framework_TestCase
{
    protected function tearDown()
    {
        parent::tearDown();
        Bootstrap::getInstance()->resetConfiguration();
    }

    public function testGetInstance()
    {
        $bootstrap = Bootstrap::getInstance();
        $this->assertSame('justso\justapi\Bootstrap', get_class($bootstrap));
    }

    public function testGetConfig()
    {
        $config = array(
            'hello' => 'world',
            'environments' => array('test' => array('approot' => '/my/approot'))
        );

        $bootstrap = Bootstrap::getInstance();
        $bootstrap->setTestConfiguration('/my/approot', $config);
        $this->assertSame($config, $bootstrap->getConfiguration());
    }

    public function testGetDomain()
    {
        $bootstrap = $this->getBootstrap();
        $config = $bootstrap->getConfiguration();
        $this->assertSame('justso.de', $config['domain']);
    }

    public function testGetAppRoot()
    {
        $bootstrap = $this->getBootstrap();
        $this->assertSame('/var/lib/jenkins/jobs/justapi/workspace', $bootstrap->getAppRoot());
    }

    public function testGetWebAppUrl()
    {
        $bootstrap = $this->getBootstrap();
        $this->assertSame('http://localhost/justapi', $bootstrap->getWebAppUrl());
    }

    public function testGetApiUrl()
    {
        $bootstrap = $this->getBootstrap();
        $this->assertSame('http://localhost/justapi/api', $bootstrap->getApiUrl());
    }

    public function testGetInstallationType()
    {
        $bootstrap = $this->getBootstrap();
        $this->assertSame('autotest', $bootstrap->getInstallationType());
    }

    public function testGetAllowedOrigins()
    {
        $bootstrap = $this->getBootstrap();
        $this->assertSame('', $bootstrap->getAllowedOrigins());
    }

    public function provideInvalidConfigurations()
    {
        return array(
            array(array()),
            array(array('environments' => array())),
            array(array('environments' => array('test' => array()))),
        );
    }

    /**
     * Tests setting an invalid configuration.
     *
     * @dataProvider provideInvalidConfigurations
     * @expectedException \justso\justapi\InvalidParameterException
     */
    public function testSetTestConfigurationWithIncompleteConfiguration($config)
    {
        $bootstrap = Bootstrap::getInstance();
        $bootstrap->setTestConfiguration('/var/www', $config);
    }

    /**
     * @param string $environment
     * @return Bootstrap
     */
    private function getBootstrap($environment = 'autotest')
    {
        $bootstrap = Bootstrap::getInstance();
        $config = array(
            'domain' => 'justso.de',
            'languages' => array('de'),
            'packages' => array(),
            "environments" => array(
                "production" => array(
                    "approot" => "/var/www/prod",
                    "appurl" => "https://justapi.justso.de",
                    "apiurl" => "https://justapi.justso.de/api"
                ),
                "integration" => array(
                    "approot" => "/var/www/test",
                    "appurl" => "https://test.justapi.justso.de",
                    "apiurl" => "https://test.justapi.justso.de/api"
                ),
                "autotest" => array(
                    "approot" => "/var/lib/jenkins/jobs/justapi/workspace",
                    "appurl" => "http://localhost/justapi",
                    "apiurl" => "http://localhost/justapi/api"
                ),
                "development" => array(
                    "approot" => "/var/www/justtexts",
                    "appurl" => "http://local.justapi.de",
                    "apiurl" => "http://local.justapi.de/api"
                ),
            ),
        );
        $bootstrap->setTestConfiguration($config['environments'][$environment]['approot'], $config);
        return $bootstrap;
    }
}
