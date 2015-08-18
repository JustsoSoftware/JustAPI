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
     * @var SystemEnvironmentInterface
     */
    private $env;

    /**
     * @param SystemEnvironmentInterface $env
     */
    public function __construct(SystemEnvironmentInterface $env)
    {
        $this->env = $env;
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
            $entry = $this->config[$name];
            if (is_callable($entry)) {
                return $entry($this->env);
            } else {
                $name = $entry;
            }
        }
        return new $name($this->env);
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
            $fs = $this->env->getFileSystem();
            if ($fs->fileExists($appRoot . $localPath)) {
                $this->config = require_once($fs->getRealPath($appRoot . $localPath));
            }
        }
    }
}
