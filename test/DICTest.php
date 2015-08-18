<?php
/**
 * Definition of class DICTest
 *
 * @copyright  2015-today Justso GmbH
 * @author     j.schirrmacher@justso.de
 * @package    justso\justapi\test
 */

namespace justso\justapi\test;

use justso\justapi\Bootstrap;
use justso\justapi\DependencyContainer;
use justso\justapi\testutil\FileSystemSandbox;

require_once(dirname(__DIR__) . '/testutil/MockClass.php');
require_once(dirname(__DIR__) . '/testutil/MockClass2.php');

/**
 * Class DICTest
 *
 * @package justso\justapi\test
 */
class DICTest extends \PHPUnit_Framework_TestCase
{
    public function testInstantiation()
    {
        $config = array('environments' => array('test' => array('approot' => '/test')));
        Bootstrap::getInstance()->setTestConfiguration('/test', $config);
        $fs = new FileSystemSandbox();
        $fs->copyFromRealFS(dirname(__DIR__) . '/testutil/TestDICConfig.php', '/test/conf/dependencies.php');
        $env = new DependencyContainer($fs);
        $object = $env->newInstanceOf('TestInterface');
        $this->assertInstanceOf('MockClass', $object);
    }
}
