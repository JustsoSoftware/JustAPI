<?php
/**
 * Definition of class DependencyContainer
 * 
 * @copyright  2015-today Justso GmbH
 * @author     j.schirrmacher@justso.de
 * @package    justso\justapi
 */

namespace justso\justapi;

/**
 * Class DependencyContainer
 *
 * @package justso\justapi
 */
class DependencyContainer implements DependencyContainerInterface
{
    /**
     * @var array
     */
    private $config = null;

    /**
     * @var FileSystemInterface
     */
    private $fs;

    /**
     * @param FileSystemInterface $fs
     */
    public function __construct(FileSystemInterface $fs)
    {
        $this->fs = $fs;
    }

    /**
     * Create new objects of a class or interface with this method.
     * It uses a mapping table to map the given $name to a implementing class, thus providing a kind of DIC.
     *
     * @param string $name
     * @return mixed
     */
    public function newInstanceOf($name)
    {
        $this->load();
        if (isset($this->config[$name])) {
            $factory = $this->config[$name];
            return $factory($this);
        }
        return new $name();
    }

    /**
     * Sets a new factory for the given name.
     *
     * @param string $name
     * @param callback $factory
     */
    public function setDICEntry($name, $factory)
    {
        $this->load();
        $this->config[$name] = $factory;
    }

    /**
     * Loads the configuration from file.
     */
    private function load()
    {
        if ($this->config === null) {
            $this->config = [];
            $localPath = '/conf/dependencies.php';
            $appRoot = Bootstrap::getInstance()->getAppRoot();
            if ($this->fs->fileExists($appRoot . $localPath)) {
                $this->config = require_once($this->fs->getRealPath($appRoot . $localPath));
            }
        }
    }
}
