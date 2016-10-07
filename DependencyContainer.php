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

    private $singletons = [];

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
     * Defines a dependency entry to be a singleton object.
     * This function should only be called in your `dependencies.php` file to make an entry being a singleton.
     * Example:
     *     'UserRepository' => $this->singleton(UserRepository::class, [$this->env])
     *
     * After that, each call to `get()` will return the singleton object, which is created only when `get()` is called
     * the first time.
     *
     * @param string $className
     * @param array $arguments for constructor
     * @return callable
     */
    public function singleton($className, $arguments)
    {
        $singletonNo = count($this->singletons) + 1;
        return function () use ($className, $arguments, $singletonNo) {
            if (!isset($this->singletons[$singletonNo])) {
                $this->singletons[$singletonNo] = new $className(...$arguments);
            }
            return $this->singletons[$singletonNo];
        };
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
                /** @noinspection PhpIncludeInspection */
                $this->config = require_once($fs->getRealPath($appRoot . $localPath));
            }
        }
    }
}
