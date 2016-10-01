<?php
/**
 * definition of class ServiceTestBase
 *
 * @copyright  2014-today Justso GmbH
 * @author     j.schirrmacher@justso.de
 * @package    justso\test
 */

namespace justso\justapi\testutil;

use justso\justapi\RequestHelper;
use justso\justauth\UserInterface;

/**
 * Base class for service tests
 *
 * @package    justso\test
 */
abstract class ServiceTestBase extends \PHPUnit\Framework\TestCase
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
        $this->assertSame('HTTP/1.0 200 Ok', $environment->getResponseCode());
        $this->assertSame(['application/json; charset=utf-8'], $environment->getResponseHeaderEntries('Content-Type'));
    }

    /**
     * Sets up a test environment.
     */
    protected function createTestEnvironment(array $params = [], array $header = [], array $server = [])
    {
        $request = new RequestHelper();
        if (!isset($server['HTTP_HOST'])) {
            $server['HTTP_HOST'] = 'localhost';
        }
        $request->fillWithData($params, $server);
        $this->env = new TestEnvironment($request, $header);
        $this->env->copyFromRealFS('/conf/dependencies.php');
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
     * @return \string[]
     * @deprecated use $this->env->getResponseCode() and $this->env->getResponseHeaderList() explicitly instead
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

    protected function mockAuthenticatedUser(UserInterface $user = null)
    {
        $auth = $this->createMock('\justso\justauth\Authenticator');
        $auth->expects($this->any())->method('isAuth')->willReturn($user !== null);
        $auth->expects($this->any())->method('getUser')->willReturn($user);
        $this->env->setDICEntry('Authenticator', $auth);
    }
}
