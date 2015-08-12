<?php
/**
 * definition of class ServiceTestBase
 *
 * @copyright  2014-today Justso GmbH
 * @author     j.schirrmacher@justso.de
 * @package    justso\test
 */

namespace justso\justapi\testutil;

use justso\justapi\Bootstrap;
use justso\justapi\RequestHelper;

/**
 * Base class for service tests
 *
 * @package    justso\test
 */
class ServiceTestBase extends \PHPUnit_Framework_TestCase
{
    /**
     * Asserts that a JSON header is sent.
     *
     * @param TestEnvironment $environment
     */
    protected function assertJSONHeader(TestEnvironment $environment)
    {
        $header = array(
            'HTTP/1.0 200 Ok',
            'Content-Type: application/json; charset=utf-8',
        );
        $this->assertEquals($header, $environment->getResponseHeader());
    }

    /**
     * Sets up a test environment.
     *
     * @param array $params
     * @param array $header
     * @return TestEnvironment
     */
    protected function createTestEnvironment(array $params = array(), array $header = array())
    {
        $request = new RequestHelper();
        $request->fillWithData($params, array('HTTP_HOST' => 'localhost'));
        return new TestEnvironment($request, $header);
    }

    /**
     * Reset configuration after tests.
     */
    protected function tearDown()
    {
        parent::tearDown();
        Bootstrap::getInstance()->resetConfiguration();
    }
}
