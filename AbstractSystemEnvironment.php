<?php
/**
 * Definition of AbstractSystemEnvironment
 *
 * @copyright  2014-today Justso GmbH
 * @author     j.schirrmacher@justso.de
 * @package    justso
 */

namespace justso\justapi;

require_once(__DIR__ . "/SystemEnvironmentInterface.php");
require_once(__DIR__ . "/DependencyContainerInterface.php");
require_once(__DIR__ . "/Bootstrap.php");

/**
 * Implements methods which are common for SystemEnvironment and TestEnvironment
 *
 * @package     justso
 */
abstract class AbstractSystemEnvironment implements SystemEnvironmentInterface, DependencyContainerInterface
{
    /**
     * Dependency Injection Container
     * @var DependencyContainerInterface
     */
    protected $dic;

    protected $bootstrap;

    public function __construct()
    {
        $this->bootstrap = new Bootstrap();

        $vendorPath = dirname(dirname(__DIR__));
        if (file_exists($vendorPath . '/autoload.php')) {
            require_once($vendorPath . '/autoload.php');
        }
        $config = $this->bootstrap->getConfiguration();
        $packages = array_merge(array('justso'), !empty($config['packages']) ? $config['packages'] : []);
        foreach ($packages as $package) {
            $autoloader = new Autoloader($package, $vendorPath);
            $autoloader->register();
        }

        $this->dic = new DependencyContainer($this);
        setlocale(LC_ALL, empty($config['locale']) ? 'de_DE.UTF-8' : $config['locale']);
    }

    /**
     * Sends a standard HTTP response.
     *
     * @param string $code    Code and Code-text
     * @param string $mime    MIME-Type
     * @param string $message Response text
     */
    public function sendResult($code, $mime, $message)
    {
        $this->sendHeader('HTTP/1.0 ' . $code);
        $this->sendHeader('Content-Type: ' . $mime);
        $this->sendResponse($message);
    }

    /**
     * Sends $data JSON encoded as a successful HTTP response.
     *
     * @param mixed $data
     */
    public function sendJSONResult($data)
    {
        $this->sendResult('200 Ok', 'application/json; charset=utf-8', json_encode($data));
    }

    /**
     * Create new objects of a class or interface with this method.
     * It uses a mapping table to map the given $name to a implementing class, thus providing a kind of DIC.
     *
     * @param string $name
     * @return object
     * @deprecated Use ->getDIC()->get() instead
     */
    public function newInstanceOf($name)
    {
        return $this->dic->newInstanceOf($name);
    }

    public function get($name, array $arguments = null)
    {
        return $this->dic->get($name, $arguments);
    }

    /**
     * Returns the dependency injection container
     *
     * @return DependencyContainerInterface
     */
    public function getDIC()
    {
        return $this->dic;
    }

    /**
     * @return Bootstrap
     */
    public function getBootstrap()
    {
        return $this->bootstrap;
    }
}
