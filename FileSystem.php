<?php
/**
 * Definition of class FileSystem
 *
 * @copyright  2014-today Justso GmbH
 * @author     j.schirrmacher@justso.de
 * @package    justso\justapi
 */

namespace justso\justapi;

/**
 * Class FileSystem
 * @package justso\justapi
 */
class FileSystem implements FileSystemInterface
{
    public function getFile($fileName)
    {
        return file_get_contents($fileName);
    }

    public function putFile($fileName, $content)
    {
        $dirName = dirname($fileName);
        if (!file_exists($dirName)) {
            $this->mkdir($dirName);
        }
        file_put_contents($fileName, $content);
    }

    public function deleteFile($fileName)
    {
        if (file_exists($fileName)) {
            unlink($fileName);
        }
    }

    public function fileExists($fileName)
    {
        return file_exists($fileName);
    }

    public function glob($pattern)
    {
        return glob($pattern);
    }

    public function removeDir($path)
    {
        rmdir($path);
    }

    public function getRealPath($path)
    {
        return $path;
    }

    public function rename($from, $to)
    {
        rename($from, $to);
    }

    public function mkdir($dirName)
    {
        mkdir($dirName, 0777, true);
    }

    public function appendLine($file, $line)
    {
        $fp = fopen($file, 'a');
        fputs($fp, $line . "\n");
        fclose($fp);
    }
}
