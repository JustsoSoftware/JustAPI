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
 * Class Session
 *
 * @package justso\justapi
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
