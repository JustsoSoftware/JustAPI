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
     * @return object
     * @deprecated use get() instead.
     */
    public function newInstanceOf($name)
    {
        return $this->get($name);
    }

    /**
     * Create new objects of a class or interface with this method.
     * It uses a mapping table to map the given $name to a implementing class, thus providing a kind of DIC.
     *
     * @param string $name
     * @param array $arguments for constructor
     * @return object
     */
    public function get($name, array $arguments = null)
    {
        $this->load();
        if ($arguments === null) {
            $arguments = [$this->env];
        }
        if (isset($this->config[$name])) {
            $entry = $this->config[$name];
            if (is_callable($entry)) {
                return $entry(...$arguments);
            } elseif (is_object($entry)) {
                return $entry;
            } else {
                $name = $entry;
            }
        }
        return new $name(...$arguments);
    }

    /**
     * Sets a new config entry for the given name.
     *
     * @param string $name
     * @param string|callback|object $entry
     */
    public function setDICEntry($name, $entry)
    {
        $this->load();
        $this->config[$name] = $entry;
    }

    /**
     * Loads the configuration from file.
     */
    private function load()
    {
        if ($this->config === null) {
            $this->config = [];
            $localPath = '/conf/dependencies.php';
            $appRoot = $this->env->getBootstrap()->getAppRoot();
            $fs = $this->env->getFileSystem();
            if ($fs->fileExists($appRoot . $localPath)) {
                $this->config = require_once($fs->getRealPath($appRoot . $localPath));
            }
        }
    }
}
