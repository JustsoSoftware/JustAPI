<?php
/**
 * definition of class RestServiceFactoryTest
 *
 * @copyright  2014 Justso GmbH
 * @author     j.schirrmacher@justso.de
 *
 * @package    justso\test
 */

namespace justso\justapi;

use justso\justapi\testutil\ServiceMock;
use justso\justapi\testutil\TestEnvironment;

/**
 * Tests the RestServiceFactory
 *
 * @package    justso\test
 */
class RestServiceFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tests that factory checks existence of service specification
     */
    public function testExistenceOfServiceSpecification()
    {
        $request = new RequestHelper();
        $request->set();
        $environment = new TestEnvironment($request);
        $factory = new RestServiceFactory($environment);
        $factory->handleRequest();
        $this->assertSame(
            array(
                'HTTP/1.0 400 Bad Request',
                'Content-Type: text/plain; charset=utf-8',
            ),
            $environment->getResponseHeader()
        );
        $this->assertSame('Missing information about service URI', $environment->getResponseContent());
    }

    /**
     * Provides testCallOfServiceMethod with method names as parameters.
     *
     * @return array
     * @codeCoverageIgnore
     */
    public function methodProvider()
    {
        return array(
            array('GET'),
            array('POST'),
            array('PUT'),
            array('DELETE'),
        );
    }

    /**
     * Tests that requested service methods are called
     * @dataProvider methodProvider
     */
    public function testCallOfService($method)
    {
        $request = new RequestHelper();
        $request->set(array(), array('PATH_INFO' => '/test', 'REQUEST_METHOD' => $method));
        $environment = new TestEnvironment($request);
        $factory = new RestServiceFactory($environment, array('test' => '\justso\justapi\testutil\ServiceMock'));
        $factory->handleRequest();
        $this->assertSame(1, ServiceMock::$called[strtolower($method) . 'Action']);
    }

    /**
     * Tests that services uses a wildcard ("*") can be called and get the actual value of the wildcard
     */
    public function testWildcardService()
    {
        $request = new RequestHelper();
        $request->set(array(), array('PATH_INFO' => '/test/123', 'REQUEST_METHOD' => 'GET'));
        $environment = new TestEnvironment($request);
        $factory = new RestServiceFactory($environment, array('test/*' => '\justso\justapi\testutil\ServiceMock'));
        $factory->handleRequest();
        $this->assertSame('test/123', ServiceMock::$lastName);
    }

    /**
     * Provides parameter to testServiceFactoryCatchesExceptions().
     *
     * @return array
     * @codeCoverageIgnore
     */
    public function exceptionProvider()
    {
        return array(
            array('\Exception', '500 Server error'),
            array('\justso\justapi\DenyException', '403 Forbidden'),
            array('\justso\justapi\InvalidParameterException', '400 Bad Request'),
        );
    }

    /**
     * Tests that the Factory reacts appropriate to exceptions.
     *
     * @param string $exception
     * @param        $errCode
     *
     * @dataProvider exceptionProvider
     */
    public function testServiceFactoryCatchesExceptions($exception, $errCode)
    {
        $request = new RequestHelper();
        $request->set(array(), array('PATH_INFO' => '/test', 'REQUEST_METHOD' => 'GET'));
        $environment = new TestEnvironment($request);
        $factory = new RestServiceFactory($environment, array('test' => '\justso\justapi\testutil\ServiceMock'));
        ServiceMock::$exception = $exception;
        $factory->handleRequest();
        $header = $environment->getResponseHeader();
        $this->assertTrue(in_array('Content-Type: text/plain; charset=utf-8', $header));
        $this->assertTrue(in_array('HTTP/1.0 ' . $errCode, $header));
    }

    /**
     * Tests how the factory reacts on unknown service names.
     */
    public function testInvalidServiceName()
    {
        $request = new RequestHelper();
        $request->set(array(), array('PATH_INFO' => '/invalid', 'REQUEST_METHOD' => 'GET'));
        $environment = new TestEnvironment($request);
        $factory = new RestServiceFactory($environment, array('test' => '\justso\justapi\testutil\ServiceMock'));
        $factory->handleRequest();
        $header = $environment->getResponseHeader();
        $this->assertTrue(in_array('Content-Type: text/plain; charset=utf-8', $header));
        $this->assertTrue(in_array('HTTP/1.0 500 Server error', $header));
    }

    public function testServerUsesRequestUri()
    {
        ServiceMock::reset();
        $request = new RequestHelper();
        $request->set(array(), array('REQUEST_URI' => '/test', 'REQUEST_METHOD' => 'GET'));
        $environment = new TestEnvironment($request);
        $factory = new RestServiceFactory($environment, array('test' => '\justso\justapi\testutil\ServiceMock'));
        $factory->handleRequest();
        $this->assertSame(1, ServiceMock::$called['getAction']);
    }

    public function testMissingRequestMethod()
    {
        $request = new RequestHelper();
        $request->set(array(), array('REQUEST_URI' => '/test'));
        $environment = new TestEnvironment($request);
        $factory = new RestServiceFactory($environment, array('test' => '\justso\justapi\testutil\ServiceMock'));
        $factory->handleRequest();
        $header = $environment->getResponseHeader();
        $this->assertTrue(in_array('Content-Type: text/plain; charset=utf-8', $header));
        $this->assertTrue(in_array('HTTP/1.0 400 Bad Request', $header));
        $this->assertSame('Missing request method', $environment->getResponseContent());
    }

    public function testMissingAction()
    {
        ServiceMock::reset();
        $request = new RequestHelper();
        $request->set(array(), array('REQUEST_URI' => '/test', 'REQUEST_METHOD' => 'UNDEFINED'));
        $environment = new TestEnvironment($request);
        $factory = new RestServiceFactory($environment, array('test' => '\justso\justapi\testutil\ServiceMock'));
        $factory->handleRequest();
        $header = $environment->getResponseHeader();
        $this->assertTrue(in_array('Content-Type: text/plain; charset=utf-8', $header));
        $this->assertTrue(in_array('HTTP/1.0 400 Bad Request', $header));
        $this->assertSame('The request method is not defined in this service', $environment->getResponseContent());
    }

    public function provideContentType()
    {
        return [
            ['application/json; charset=utf-8'],
            ['application/x-www-form-urlencoded'],
            ['multipart/form-data']
        ];
    }

    /**
     * @param $type
     * @dataProvider provideContentType
     */
    public function testContentType($type)
    {
        ServiceMock::reset();
        $request = new RequestHelper();
        $request->set(array(), array('REQUEST_URI' => '/test', 'REQUEST_METHOD' => 'GET', 'CONTENT_TYPE' => $type));
        $environment = new TestEnvironment($request);
        $factory = new RestServiceFactory($environment, array('test' => '\justso\justapi\testutil\ServiceMock'));
        $factory->handleRequest();
    }
}
