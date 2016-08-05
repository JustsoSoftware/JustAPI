<?php
/**
 * Definition of class DICTest
 *
 * @copyright  2015-today Justso GmbH
 * @author     j.schirrmacher@justso.de
 * @package    justso\justapi\test
 */

namespace justso\justapi;

use justso\justapi\testutil\MockClass;
use justso\justapi\testutil\MockClass2;
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
     * @var DependencyContainer
     */
    private $dic;

    public function setUp()
    {
        parent::setUp();
        $env = new TestEnvironment(new RequestHelper());
        $this->dic = $env->getDIC();
    }

    public function testInstantiationWithUnregisteredClassName()
    {
        $object = $this->dic->get('justso\justapi\testutil\MockClass2');
        $this->assertInstanceOf('justso\justapi\testutil\MockClass2', $object);
    }

    public function testInstantiationWithRegisteredClassName()
    {
        $this->dic->setDICEntry('TestClassName', 'justso\justapi\testutil\MockClass2');
        $object = $this->dic->get('TestClassName');
        $this->assertInstanceOf('justso\justapi\testutil\MockClass2', $object);
    }

    public function testInstantiationWithFactory()
    {
        $this->dic->setDICEntry('TestFactory', function () {
            /** @var MockClass2 $object */
            $object = $this->dic->get('justso\justapi\testutil\MockClass2');
            return new MockClass($object);
        });
        $object = $this->dic->get('TestFactory');
        $this->assertInstanceOf('justso\justapi\testutil\MockClass', $object);
    }
    public function testInstantiationWithObject()
    {
        $this->dic->setDICEntry('TestObject', new MockClass2());
        $object = $this->dic->get('TestObject');
        $this->assertInstanceOf('justso\justapi\testutil\MockClass2', $object);
    }

    public function testArguments()
    {
        $object = $this->dic->get('justso\justapi\testutil\MockClass2', ['test', 4711]);
        $this->assertSame(['test', 4711], $object->myParams);
    }
}
