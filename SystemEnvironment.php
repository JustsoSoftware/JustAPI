<?php
/**
 * Definition of SystemEnvironment
 *
 * @copyright  2013-today Justso GmbH
 * @author     j.schirrmacher@justso.de
 * @package    justso
 */

namespace justso\justapi;

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
    private $header;

    /**
     * Initializes the SystemEnvironment
     */
    public function __construct()
    {
        $this->request = new RequestHelper();
        if (function_exists('apache_request_headers')) {
            $this->header  = apache_request_headers();
        }
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
}
