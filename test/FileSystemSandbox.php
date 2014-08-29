<?php
/**
 * Definition of class FileSystemSandbox
 * 
 * @copyright  2014-today Justso GmbH
 * @author     j.schirrmacher@justso.de
 * @package    justso\justapi\test
 */

namespace justso\justapi\test;

use justso\justapi\FileSystem;
use justso\justapi\FileSystemInterface;

/**
 * Class FileSystemSandbox
 * @package justso\justapi\test
 */
class FileSystemSandbox implements FileSystemInterface
{
    /**
     * Path prefix for sandbox
     * @var string
     */
    private $path;

    /**
     * The 'real' file system the sandbox is working on.
     * @var FileSystemInterface
     */
    private $realFileSystem;

    private $protocol = array();

    /**
     * Initializes the sandbox.
     */
    public function __construct()
    {
        $this->path = '/tmp/sandbox/' . microtime(true);
        mkdir($this->path, 0777, true);
        $this->realFileSystem = new FileSystem();
    }

    /**
     * Remove the sandbox completely.
     */
    public function __destruct()
    {
        $this->resetProtocol();
        $this->rmdir($this->path);
    }

    /**
     * Clean up sandbox.
     */
    public function cleanUpSandbox()
    {
        $this->resetProtocol();
        $this->rmdir($this->path);
        mkdir($this->path, 0777, true);
    }

    /**
     * Returns a list of protocol entries.
     *
     * @return array
     */
    public function getProtocol()
    {
        return $this->protocol;
    }

    /**
     * Empties the protocol list.
     */
    public function resetProtocol()
    {
        // echo `find {$this->path} -type f`;
        $this->protocol = array();
    }

    public function putFile($fileName, $content)
    {
        $this->protocol[] = "putFile($fileName)";
        $this->realFileSystem->putFile($this->path . $this->makeAbsolute($fileName), $content);
    }

    public function deleteFile($fileName)
    {
        $this->protocol[] = "deleteFile($fileName)";
        $this->realFileSystem->deleteFile($this->path . $this->makeAbsolute($fileName));
    }

    public function getFile($fileName)
    {
        $this->protocol[] = "getFile($fileName)";
        return $this->realFileSystem->getFile($this->path . $this->makeAbsolute($fileName));
    }

    public function fileExists($fileName)
    {
        // echo "Testing " . $this->path . $this->makeAbsolute($fileName) . "\n";
        $exists = $this->realFileSystem->fileExists($this->path . $this->makeAbsolute($fileName));
        $this->protocol[] = "fileExists($fileName) -> " . ($exists ? 'true' : 'false');
        return $exists;
    }

    /**
     * Makes a path absolute.
     * In contrast to realpath(), it works with non-existing files and pathes as well.
     *
     * @param string $path
     * @return string
     */
    private function makeAbsolute($path) {
        $path = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $path);
        $parts = array_filter(explode(DIRECTORY_SEPARATOR, $path), 'strlen');
        $absolutes = array();
        foreach ($parts as $part) {
            if ('.' == $part) {
                continue;
            } elseif ('..' == $part) {
                array_pop($absolutes);
            } else {
                $absolutes[] = $part;
            }
        }
        return DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $absolutes);
    }

    /**
     * Recursively delete a directory and its content.
     *
     * @param string $dirPath
     */
    private function rmdir($dirPath)
    {
        $directoryIterator = new \RecursiveDirectoryIterator($dirPath, \FilesystemIterator::SKIP_DOTS);
        $iterator = new \RecursiveIteratorIterator($directoryIterator, \RecursiveIteratorIterator::CHILD_FIRST);
        foreach($iterator as $path) {
            /** @var $path \DirectoryIterator */
            if ($path->isDir()) {
                rmdir($path->getPathname());
            } else {
                unlink($path->getPathname());
            }
        }
        rmdir($dirPath);
    }

    /**
     * Copies the content of a file from the real file system to the test file system.
     *
     * @param string $fileName    Source file name with path
     * @param string $destination Destination path or null if it should be the same path as the source
     */
    public function copyFromRealFS($fileName, $destination = null)
    {
        $this->putFile($destination ?: $fileName, $this->realFileSystem->getFile($fileName));
    }
}
