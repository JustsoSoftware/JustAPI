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
use justso\justapi\FileSystemInterface;
use justso\justapi\RequestHelper;
use justso\justapi\SessionInterface;

require_once(dirname(__DIR__) . "/AbstractSystemEnvironment.php");

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

    /** @var string */
    private $stdin;

    private $responseCode;

    /**
     * @var string[]
     */
    private $responseHeader = [];

    /**
     * @var string
     */
    private $responseContent = '';

    private $fileSystem = null;

    /**
     * @var SessionInterface
     */
    private $session;

    /**
     * @param RequestHelper $request
     * @param string[] $header
     * @param string $stdin
     */
    public function __construct(RequestHelper $request = null, array $header = array(), $stdin = '')
    {
        $this->request = $request;
        $this->header  = $header;
        $this->stdin   = $stdin;
        parent::__construct();
        $this->session = new TestSession();
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
        $comp = explode(':', $header, 2);
        if (isset($comp[1])) {
            $this->responseHeader[] = trim($comp[0]) . ': ' . trim($comp[1]);
        } else {
            $this->responseCode = $header;
        }
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
        $header = $this->responseHeader;
        array_unshift($header, $this->responseCode);
        return $header;
    }

    public function getResponseHeaderList()
    {
        return $this->responseHeader;
    }

    public function getResponseCode()
    {
        return $this->responseCode;
    }

    public function getResponseHeaderEntries($key)
    {
        $result = [];
        foreach ($this->responseHeader as $row) {
            $comp = explode(':', $row, 2);
            if (trim($comp[0]) === $key) {
                $result[] = trim($comp[1]);
            }
        }
        return $result;
    }

    /**
     * Clears the response to make the environment usable for another request
     */
    public function clearResponse()
    {
        $this->responseContent = '';
        $this->responseHeader = array();
        $this->responseCode = '200 Ok';
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
     * Sets a new factory for the given name.
     *
     * @param string   $name
     * @param callback $func
     */
    public function setDICEntry($name, $func)
    {
        $this->dic->setDICEntry($name, $func);
    }

    /**
     * @param string $localPath
     */
    public function copyFromRealFS($localPath)
    {
        /** @var FileSystemSandbox $fs */
        $fs = $this->getFileSystem();
        $fs->copyFromRealFS($this->getBootstrap()->getAppRoot() . $localPath);
    }

    public function getStdInput()
    {
        return $this->stdin;
    }
}
