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
        return $_SESSION[$name];
    }

    public function setValue($name, $value)
    {
        $_SESSION[$name] = $value;
    }

    public function getId()
    {
        return session_id();
    }

    public function isValueSet($name)
    {
        return isset($_SESSION[$name]);
    }

    public function activate()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
}
