<?php
/**
 * Definition of TestEnvironment
 *
 * @copyright  2013-today Justso GmbH
 * @author     j.schirrmacher@justso.de
 * @package    justso\test
 */

namespace justso\justapi\test;

use justso\justapi\AbstractSystemEnvironment;
use justso\justapi\RequestHelper;

/**
 * Handles the outer world interface for tests
 *
 * @package    justso
 */
class TestEnvironment extends AbstractSystemEnvironment
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
     * @var string[]
     */
    private $responseHeader = array();

    /**
     * @var string
     */
    private $responseContent = '';

    /**
     * @param RequestHelper $request
     * @param string[] $header
     */
    public function __construct(RequestHelper $request, array $header = array())
    {
        $this->request = $request;
        $this->header  = $header;
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
        $this->responseHeader[] = $header;
    }

    /**
     * @param string $response
     */
    public function sendResponse($response)
    {
        $this->responseContent .= $response;
    }

    /**
     * @return string
     */
    public function getResponseContent()
    {
        return $this->responseContent;
    }

    /**
     * @return string[]
     */
    public function getResponseHeader()
    {
        return $this->responseHeader;
    }

    /**
     * Clears the response to make the environment usable for another request
     */
    public function clearResponse()
    {
        $this->responseContent = '';
        $this->responseHeader = array();
    }

    /**
     * Changes the current user account. The permissions of this account are used for subsequent calls in this request.
     *
     * @param int $user key of user
     */
    public function switchUser($user)
    {
        // Don't do anything in tests
    }
}
