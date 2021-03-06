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
     * @param string $method
     */
    public function testCallOfService($method)
    {
        $request = new RequestHelper();
        $request->set(array(), array('REQUEST_URI' => '/api/test', 'REQUEST_METHOD' => $method));
        $environment = new TestEnvironment($request);
        $factory = new RestServiceFactory($environment, array('/api/test' => '\justso\justapi\testutil\ServiceMock'));
        $factory->handleRequest();
        $this->assertSame(1, ServiceMock::$called[strtolower($method) . 'Action']);
    }

    /**
     * Tests that services uses a wildcard ("*") can be called and get the actual value of the wildcard
     */
    public function testWildcardService()
    {
        $request = new RequestHelper();
        $request->set(array(), array('REQUEST_URI' => '/api/test/123', 'REQUEST_METHOD' => 'GET'));
        $environment = new TestEnvironment($request);
        $factory = new RestServiceFactory($environment, array('/api/test/*' => '\justso\justapi\testutil\ServiceMock'));
        $factory->handleRequest();
        $this->assertSame('/api/test/123', ServiceMock::$lastName);
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
        $request->set(array(), array('REQUEST_URI' => '/test', 'REQUEST_METHOD' => 'GET'));
        $environment = new TestEnvironment($request);
        $factory = new RestServiceFactory($environment, array('/test' => '\justso\justapi\testutil\ServiceMock'));
        ServiceMock::$exception = $exception;
        $factory->handleRequest();
        $this->assertSame('HTTP/1.0 ' . $errCode, $environment->getResponseCode(), $environment->getResponseContent());
        $header = $environment->getResponseHeaderEntries('Content-Type');
        $this->assertSame(['text/plain; charset=utf-8'], $header, $environment->getResponseContent());
    }

    /**
     * Tests how the factory reacts on unknown service names.
     */
    public function testInvalidServiceName()
    {
        $request = new RequestHelper();
        $request->set(array(), array('REQUEST_URI' => '/invalid', 'REQUEST_METHOD' => 'GET'));
        $environment = new TestEnvironment($request);
        $factory = new RestServiceFactory($environment, array('/test' => '\justso\justapi\testutil\ServiceMock'));
        $factory->handleRequest();
        $header = $environment->getResponseHeader();
        $this->assertTrue(in_array('Content-Type: text/plain; charset=utf-8', $header));
        $this->assertTrue(in_array('HTTP/1.0 500 Server error', $header));
    }

    public function testMissingRequestMethod()
    {
        $request = new RequestHelper();
        $request->set(array(), array('REQUEST_URI' => '/test'));
        $environment = new TestEnvironment($request);
        $factory = new RestServiceFactory($environment, array('/test' => '\justso\justapi\testutil\ServiceMock'));
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
        $factory = new RestServiceFactory($environment, array('/test' => '\justso\justapi\testutil\ServiceMock'));
        $factory->handleRequest();
        $header = $environment->getResponseHeader();
        $this->assertTrue(in_array('Content-Type: text/plain; charset=utf-8', $header));
        $this->assertTrue(in_array('HTTP/1.0 400 Bad Request', $header));
        $this->assertSame('The request method is not defined in this service', $environment->getResponseContent());
    }

    /**
     * @codeCoverageIgnore
     * @return array
     */
    public function provideContentType()
    {
        return [
            ['application/json; charset=utf-8', '{"test": "123"}'],
            ['application/x-www-form-urlencoded', 'test=123&data=456'],
            ['multipart/form-data; boundary=qwertz', str_replace("\n", "\r\n", <<<'DATA'
--qwertz
content-disposition: form-data; name="test"

123
--qwertz
content-disposition: form-data; name="data[]"

456
--qwertz
content-disposition: form-data; name="data[]"

789
--qwertz
content-disposition: form-data; name="testfile"; filename="myTestFile.txt"
Content-Type: text/plain
Content-Transfer-Encoding: binary

Content of the test file
--qwertz--

DATA
            )]
        ];
    }

    /**
     * @param string $type
     * @param string $stdin
     * @dataProvider provideContentType
     */
    public function testContentType($type, $stdin)
    {
        ServiceMock::reset();
        $request = new RequestHelper();
        $request->set([], ['REQUEST_URI' => '/test', 'REQUEST_METHOD' => 'GET', 'CONTENT_TYPE' => $type]);
        $environment = new TestEnvironment($request, [], $stdin);
        $factory = new RestServiceFactory($environment, array('/test' => '\justso\justapi\testutil\ServiceMock'));
        $factory->handleRequest();
        $this->assertSame('', $environment->getResponseContent());
    }
}
