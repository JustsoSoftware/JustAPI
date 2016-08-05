<?php
/**
 * Definition of SystemEnvironment
 *
 * @copyright  2013-today Justso GmbH
 * @author     j.schirrmacher@justso.de
 * @package    justso
 */

namespace justso\justapi;

// @codeCoverageIgnoreStart
require_once(__DIR__ . "/AbstractSystemEnvironment.php");
//@codeCoverageIgnoreEnd

/**
 * Handles the outer world interface to the browser
 *
 * @copyright  2013-today Justso GmbH
 * @author     j.schirrmacher@justso.de
 * @package    justso
 * @codeCoverageIgnore
 */
class SystemEnvironment extends AbstractSystemEnvironment
{
    /**
     * @var RequestHelper
     */
    private $request;

    /**
     * @var string[]
     */
    private $header = array();

    /**
     * @var Session
     */
    private $session;

    /**
     * Initializes the SystemEnvironment
     */
    public function __construct()
    {
        parent::__construct();
        $this->request = new RequestHelper();
        if (function_exists('apache_request_headers')) {
            $this->header  = apache_request_headers();
        }
        $this->session = new Session();
    }

    /**
     * @return RequestHelper
     */
    public function getRequestHelper()
    {
        return $this->request;
    }

    /**
     * @return string[]
     */
    public function getHeader()
    {
        return $this->header;
    }

    /**
     * @param string $header
     */
    public function sendHeader($header)
    {
        header($header);
    }

    /**
     * @param string $response
     */
    public function sendResponse($response)
    {
        echo $response;
    }

    /**
     * Changes the current user account. The permissions of this account are used for subsequent calls in this request.
     *
     * @param int $user key of user
     */
    public function switchUser($user)
    {
    }

    /**
     * @return FileSystemInterface
     */
    public function getFileSystem()
    {
        return new FileSystem();
    }

    /**
     * @param string $name
     * @param string $value
     * @param int $expire
     * @param string $path
     * @param string $domain
     * @param bool $secure
     * @param bool $httpOnly
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
    ) {
        return setcookie($name, $value, $expire, $path, $domain, $secure, $httpOnly);
    }

    /**
     * @return Session
     */
    public function getSession()
    {
        return $this->session;
    }
}
