<?php
/**
 * Definition of class DICTest
 *
 * @copyright  2015-today Justso GmbH
 * @author     j.schirrmacher@justso.de
 * @package    justso\justapi\test
 */

namespace justso\justapi;

use justso\justapi\testutil\FileSystemSandbox;
use justso\justapi\testutil\TestEnvironment;

require_once(dirname(__DIR__) . '/testutil/MockClass.php');
require_once(dirname(__DIR__) . '/testutil/MockClass2.php');

/**
 * Class DICTest
 *
 * @package justso\justapi\test
 */
class DICTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Bootstrap
     */
    private $bootstrap;

    public function testInstantiation()
    {
        $request = new RequestHelper();
        $env = new TestEnvironment($request);
        $config = array('environments' => array('test' => array('approot' => '/test')));
        $env->getBootstrap()->setTestConfiguration('/test', $config);

        /** @var FileSystemSandbox $fs */
        $fs = $env->getFileSystem();
        $fs->copyFromRealFS(dirname(__DIR__) . '/testutil/TestDICConfig.php', '/test/conf/dependencies.php');
        $env = new DependencyContainer($env);

        $object = $env->newInstanceOf('TestClassName');
        $this->assertInstanceOf('justso\justapi\testutil\MockClass2', $object);

        $object = $env->newInstanceOf('TestFactory');
        $this->assertInstanceOf('justso\justapi\testutil\MockClass', $object);

        $object = $env->newInstanceOf('TestObject');
        $this->assertInstanceOf('justso\justapi\testutil\MockClass2', $object);
    }
}
