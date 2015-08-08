<?php
/**
 * Definition of class RequestHelperTest
 *
 * @copyright  2014-today Justso GmbH
 * @author     j.schirrmacher@justso.de
 * @package    justso\justapi\test
 */

namespace justso\justapi\test;

use justso\justapi\RequestHelper;

/**
 * Class RequestHelperTest
 * @package justso\justapi\test
 */
class RequestHelperTest extends \PHPUnit_Framework_TestCase
{
    public function testGetParam()
    {
        $request = new RequestHelper();
        $request->set(array('foo' => 'bar'));
        $this->assertSame('bar', $request->getParam('foo'));
    }

    public function testDefault()
    {
        $request = new RequestHelper();
        $request->set();
        $this->assertSame('def', $request->getParam('foo', 'def', true));
        $this->assertSame('def', $request->getIdentifierParam('foo', 'def', true));
    }

    public function testDefaultIsIgnoredWhenParameterIsSet()
    {
        $request = new RequestHelper();
        $request->set(array('foo' => 'bar'));
        $this->assertSame('bar', $request->getParam('foo', 'def', true));
    }

    /**
     * @expectedException \justso\justapi\InvalidParameterException
     */
    public function testThatUndefinedParameterFails()
    {
        $request = new RequestHelper();
        $request->set();
        $request->getParam('foo');
        // @codeCoverageIgnoreStart
    }
    // @codeCoverageIgnoreEnd

    public function testGetIdentifierParam()
    {
        $request = new RequestHelper();
        $request->set(array('foo' => 'bar'));
        $this->assertSame('bar', $request->getIdentifierParam('foo'));
    }

    public function testGetIntParam()
    {
        $request = new RequestHelper();
        $request->set(array('foo' => '123'));
        $this->assertSame(123, $request->getIntParam('foo'));
        $request->set(array('foo' => '-123'));
        $this->assertSame(-123, $request->getIntParam('foo'));
        $request->set(array('foo' => '0'));
        $this->assertSame(0, $request->getIntParam('foo'));
    }

    public function testGetBooleanParam()
    {
        $request = new RequestHelper();
        $request->set(array('foo' => 'true'));
        $this->assertSame(true, $request->getBooleanParam('foo'));
        $request->set(array('foo' => 'false'));
        $this->assertSame(false, $request->getBooleanParam('foo'));
    }

    public function testGetKeyParam()
    {
        $request = new RequestHelper();
        $request->set(array('foo' => '123'));
        $this->assertSame(123, $request->getKeyParam('foo'));
    }

    public function testGetEMailParam()
    {
        $request = new RequestHelper();
        $request->set(array('foo' => 'tech@justso.de'));
        $this->assertSame('tech@justso.de', $request->getEMailParam('foo'));
    }

    public function testGetFileParam()
    {
        $request = new RequestHelper();
        $request->set(array('foo' => array('a')));
        $this->assertSame(array('a'), $request->getFileParam('foo'));
    }

    /**
     * Provides data for testThrowExceptionWhenUsedWithWrongParameter()
     * @return array
     * @codeCoverageIgnore
     */
    public function provideParameterTestingFunctionNames()
    {
        return array(
            array('getIdentifierParam', '%'),
            array('getIntParam', 'a'),
            array('getBooleanParam', 'a'),
            array('getKeyParam', 'a'),
            array('getKeyParam', '0'),
            array('getKeyParam', '-123'),
            array('getEMailParam', 'a'),
            array('getEMailParam', '@'),
            array('getFileParam', '@'),
        );
    }

    /**
     * @dataProvider provideParameterTestingFunctionNames
     * @expectedException \justso\justapi\InvalidParameterException
     */
    public function testThrowsExceptionWhenUsedWithWrongParameter($func, $val)
    {
        $request = new RequestHelper();
        $request->set(array('foo' => $val));
        $request->$func('foo');
        // @codeCoverageIgnoreStart
    }
    // @codeCoverageIgnoreEnd

    public function testIsParamSet()
    {
        $request = new RequestHelper();
        $request->set();
        $this->assertFalse($request->isParamSet('foo'));
        $request->set(array('foo' => 'bar'));
        $this->assertTrue($request->isParamSet('foo'));
    }
}
