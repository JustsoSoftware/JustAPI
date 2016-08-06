<?php
/**
 * Definition of class BootstrapTest
 *
 * @copyright  2014-today Justso GmbH
 * @author     j.schirrmacher@justso.de
 * @package    justso\justapi\test
 */

namespace justso\justapi;

/**
 * Class BootstrapTest
 *
 * @package justso\justapi\test
 */
class BootstrapTest extends \PHPUnit_Framework_TestCase
{
    public function testGetInstance()
    {
        $bootstrap = new Bootstrap();
        $this->assertSame('justso\justapi\Bootstrap', get_class($bootstrap));
    }

    public function testGetConfig()
    {
        $config = array(
            'hello' => 'world',
            'environments' => array('test' => array('approot' => '/my/approot'))
        );

        $bootstrap = new Bootstrap('/my/approot', $config);
        $this->assertSame($config, $bootstrap->getConfiguration());
    }

    public function testGetDomain()
    {
        $config = $this->getBootstrap()->getConfiguration();
        $this->assertSame('justso.de', $config['domain']);
    }

    public function testGetAppRoot()
    {
        $this->assertSame('/var/lib/jenkins/jobs/justapi/workspace', $this->getBootstrap()->getAppRoot());
    }

    public function testGetWebAppUrl()
    {
        $this->assertSame('http://localhost/justapi', $this->getBootstrap()->getWebAppUrl());
    }

    public function testGetApiUrl()
    {
        $this->assertSame('http://localhost/justapi/api', $this->getBootstrap()->getApiUrl());
    }

    public function testGetInstallationType()
    {
        $this->assertSame('autotest', $this->getBootstrap()->getInstallationType());
    }

    public function testGetAllowedOrigins()
    {
        $this->assertSame('', $this->getBootstrap()->getAllowedOrigins());
    }

    /**
     * Provides data for testSetTestConfigurationWithIncompleteConfiguration
     * @return array
     * @codeCoverageIgnore
     */
    public function provideInvalidConfigurations()
    {
        return array(
            array(array('environments' => array())),
            array(array('environments' => array('test' => array()))),
            array(array('environments' => array('test' => array('approot' => '/unknown/path')))),
        );
    }

    /**
     * Tests setting an invalid configuration.
     *
     * @dataProvider provideInvalidConfigurations
     * @expectedException \justso\justapi\InvalidParameterException
     * @param $config
     */
    public function testSetTestConfigurationWithIncompleteConfiguration($config)
    {
        new Bootstrap('/var/www', $config);
        // @codeCoverageIgnoreStart
    }
    // @codeCoverageIgnoreEnd

    /**
     * @param string $environment
     * @return Bootstrap
     */
    private function getBootstrap($environment = 'autotest')
    {
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
        return new Bootstrap($config['environments'][$environment]['approot'], $config);
    }
}
