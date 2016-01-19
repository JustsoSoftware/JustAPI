<?php
/**
 * Definition of class Session
 *
 * @copyright  2015-today Justso GmbH
 * @author     j.schirrmacher@justso.de
 * @package    justso\justapi
 */

namespace justso\justapi;

/**
 * This class abstracts php session handling to prevent usage of super globals and instead use a normal object
 *
 * @codeCoverageIgnore
 */
class Session implements SessionInterface
{
    public function getValue($name)
    {
        $this->activate();
        return $_SESSION[$name];
    }

    public function setValue($name, $value)
    {
        $this->activate();
        $_SESSION[$name] = $value;
    }

    public function unsetValue($name)
    {
        if ($this->isValueSet($name)) {
            unset($_SESSION[$name]);
        }
    }

    public function getId()
    {
        $this->activate();
        return session_id();
    }

    public function isValueSet($name)
    {
        $this->activate();
        return isset($_SESSION[$name]);
    }

    public function activate()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
}
