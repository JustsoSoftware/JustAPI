<?php
/**
 * Definition of SystemEnvironmentInterface
 *
 * @copyright  2013-today Justso GmbH
 * @author     j.schirrmacher@justso.de
 * @package    justso
 */

namespace justso\justapi;

/**
 * API of classes handling the system environment like headers, requests and output
 */
interface SystemEnvironmentInterface
{
    /**
     * @return FileSystemInterface
     */
    public function getFileSystem();

    /**
     * @return RequestHelper
     */
    public function getRequestHelper();

    public function getHeader();
    public function sendHeader($header);

    /**
     * @param string $name
     * @param string $value
     * @param int    $expire
     * @param string $path
     * @param string $domain
     * @param bool   $secure
     * @param bool   $httpOnly
     * @return bool
     */
    public function sendCookie(
        $name,
        $value = null,
        $expire = null,
        $path = null,
        $domain = null,
        $secure = null,
        $httpOnly = null
    );

    public function sendResponse($response);

    /**
     * Changes the current user account. The permissions of this account are used for subsequent calls in this request.
     *
     * @param int $user key of user
     * @return void
     */
    public function switchUser($user);

    /**
     * Sends a standard HTTP response.
     *
     * @param string $code    Code and Code-text
     * @param string $mime    MIME-Type
     * @param string $message Response text
     */
    public function sendResult($code, $mime, $message);

    /**
     * Sends $data JSON encoded as a successful HTTP response.
     *
     * @param mixed $data
     */
    public function sendJSONResult($data);

    /**
     * @return Session
     */
    public function getSession();

    /**
     * Create new objects of a class or interface with this method.
     * It uses a mapping table to map the given $name to a implementing class, thus providing a kind of DIC.
     *
     * @param string $name
     * @return object
     */
    public function newInstanceOf($name);
}
