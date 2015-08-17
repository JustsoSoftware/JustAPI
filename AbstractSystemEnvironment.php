<?php
/**
 * Definition of AbstractSystemEnvironment
 *
 * @copyright  2014-today Justso GmbH
 * @author     j.schirrmacher@justso.de
 * @package    justso
 */

namespace justso\justapi;

/**
 * Implements methods which are common for SystemEnvironment and TestEnvironment
 *
 * @package     justso
 */
abstract class AbstractSystemEnvironment implements SystemEnvironmentInterface, DependencyContainerInterface
{
    /**
     * Sends a standard HTTP response.
     *
     * @param string $code    Code and Code-text
     * @param string $mime    MIME-Type
     * @param string $message Response text
     */
    public function sendResult($code, $mime, $message)
    {
        $this->sendHeader('HTTP/1.0 ' . $code);
        $this->sendHeader('Content-Type: ' . $mime);
        $this->sendResponse($message);
    }

    /**
     * Sends $data JSON encoded as a successful HTTP response.
     *
     * @param mixed $data
     */
    public function sendJSONResult($data)
    {
        $this->sendResult('200 Ok', 'application/json; charset=utf-8', json_encode($data));
    }
}
