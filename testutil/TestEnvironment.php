<?php
/**
 * Definition of TestEnvironment
 *
 * @copyright  2013-today Justso GmbH
 * @author     j.schirrmacher@justso.de
 * @package    justso\test
 */

namespace justso\justapi\testutil;

use justso\justapi\AbstractSystemEnvironment;
use justso\justapi\DependencyContainer;
use justso\justapi\FileSystemInterface;
use justso\justapi\RequestHelper;
use justso\justapi\SessionInterface;

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

    private $fileSystem = null;

    private $dic;

    /**
     * @var SessionInterface
     */
    private $session;

    /**
     * @param RequestHelper $request
     * @param string[] $header
     */
    public function __construct(RequestHelper $request, array $header = array())
    {
        $this->request = $request;
        $this->header  = $header;
        $this->session = new TestSession();
        $this->dic     = new DependencyContainer($this);
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
     * @codeCoverageIgnore
     */
    public function switchUser($user)
    {
        // Don't do anything in tests
    }

    /**
     * @return FileSystemInterface
     */
    public function getFileSystem()
    {
        if ($this->fileSystem === null) {
            $this->fileSystem = new FileSystemSandbox();
        }
        return $this->fileSystem;
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
        $this->sendHeader("Cookie-Set: $name=$value; $expire; $path; $domain; $secure; $httpOnly");
        return true;
    }

    /**
     * @return SessionInterface
     */
    public function getSession()
    {
        return $this->session;
    }

    /**
     * Create new objects of a class or interface with this method.
     * It uses a mapping table to map the given $name to a implementing class, thus providing a kind of DIC.
     *
     * @param string $name
     * @return object
     */
    public function newInstanceOf($name)
    {
        return $this->dic->newInstanceOf($name);
    }

    /**
     * Sets a new factory for the given name.
     *
     * @param string   $name
     * @param callback $func
     */
    public function setDICEntry($name, $func)
    {
        $this->dic->setDICEntry($name, $func);
    }
}
