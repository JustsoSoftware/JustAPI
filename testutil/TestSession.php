<?php
/**
 * Definition of class TestSession
 *
 * @copyright  2015-today Justso GmbH
 * @author     j.schirrmacher@justso.de
 * @package    justso\justapi\test
 */

namespace justso\justapi\testutil;

use justso\justapi\SessionInterface;

/**
 * Class TestSession
 *
 * @package justso\justapi\test
 */
class TestSession implements SessionInterface
{
    public $session = array();

    public function getValue($name)
    {
        return $this->session[$name];
    }

    public function setValue($name, $value)
    {
        $this->session[$name] = $value;
    }

    public function unsetValue($name)
    {
        if ($this->isValueSet($name)) {
            unset($this->session[$name]);
        }
    }

    public function getId()
    {
        return 'test session';
    }

    public function isValueSet($name)
    {
        return isset($this->session[$name]);
    }

    public function activate()
    {
    }
}
