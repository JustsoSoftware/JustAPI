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
abstract class ServiceTestBase extends \PHPUnit_Framework_TestCase
{
    /** @var TestEnvironment */
    protected $env;

    /**
     * Asserts that a JSON header is sent.
     *
     * @param TestEnvironment $environment
     */
    protected function assertJSONHeader(TestEnvironment $environment)
    {
        $header = $this->parseHttpHeaders($environment->getResponseHeader());
        $this->assertSame('HTTP/1.0 200 Ok', $header[0]);
        $this->assertSame('application/json; charset=utf-8', $header['Content-Type']);
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
        $this->env = new TestEnvironment($request, $header);
        return $this->env;
    }

    /**
     * Reset configuration after tests.
     */
    protected function tearDown()
    {
        parent::tearDown();
        if ($this->env) {
            $this->env->getBootstrap()->resetConfiguration();
        }
    }

    /**
     * Mocks an entry in the DependencyContainer naming an interface.
     *
     * @param string          $namespace
     * @param string          $name
     * @param TestEnvironment $env
     * @return \PHPUnit_FrameWork_MockObject_MockObject
     */
    public function mockInterface($namespace, $name, TestEnvironment $env)
    {
        $mock = $this->getMockForAbstractClass(rtrim($namespace, '\\') . '\\' . $name);
        $env->setDICEntry($name, function () use ($mock) {
            return $mock;
        });
        return $mock;
    }

    /**
     * @param array $raw_headers
     * @return array
     */
    protected function parseHttpHeaders(array $raw_headers)
    {
        $headers = [];
        $key = '';

        foreach ($raw_headers as $row) {
            $comp = explode(':', $row, 2);
            if (isset($comp[1])) {
                $key = $comp[0];
                $val = trim($comp[1]);
                if (isset($headers[$key])) {
                    if (!is_array($headers[$key])) {
                        $headers[$key] = [$headers[$key]];
                    }
                    $headers[$key] = array_merge($headers[$key], [$val]);
                }
                $headers[$key] = $val;
            } else {
                if (substr($row, 0, 1) === "\t") {
                    $headers[$key] .= "\r\n\t" . trim($row);
                } elseif (!$key) {
                    $headers[0] = trim($row);
                }
            }
        }

        return $headers;
    }
}
