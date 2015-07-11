<?php
/**
 * Definition of FileSystemInterface.php
 *
 * @copyright  2014-today Justso GmbH
 * @author     j.schirrmacher@justso.de
 */

namespace justso\justapi;

/**
 * Class FileSystem
 * @package justso\justapi
 */
interface FileSystemInterface
{
    public function putFile($fileName, $content);

    public function deleteFile($fileName);

    public function getFile($fileName);

    public function fileExists($fileName);

    public function glob($pattern);

    public function mkdir($dirName);

    public function removeDir($path);

    public function getRealPath($path);

    public function rename($from, $to);
}
