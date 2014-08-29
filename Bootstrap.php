<?php
/**
 * Definition of class Bootstrap
 *
 * @copyright  2014-today Justso GmbH, Frankfurt, Germany
 * @author     j.schirrmacher@justso.de
 *
 * @package    justso
 */

namespace justso\justapi;

require_once("Autoloader.php");
require_once("InvalidParameterException.php");

/**
 * Singleton class which sets up autoloading
 *
 * @package    justso
 */
class Bootstrap
{
    /**
     * @var Bootstrap
     */
    private static $instance = null;

    private static $appRoot;

    private static $config;

    private static $environment;

    private static $info;

    /**
     * Initializes the system.
     * The file 'config.json' in the application folder is read and autoloading of the specified packages is configured.
     * @codeCoverageIgnore
     */
    private function __construct()
    {
        $this->resetConfiguration();
        $packages = array_merge(array('justso'), self::$config['packages']);
        foreach ($packages as $package) {
            $autoloader = new Autoloader($package, self::$appRoot . '/vendor');
            $autoloader->register();
        }
    }

    /**
     * Sets the configuration for testing purposes - don't use for other things.
     * Autoloading is initialized only on boot and will not be changed afterwards.
     *
     * @param string $appRoot Simulated application root folder
     * @param mixed  $config  New test configuration
     */
    public function setTestConfiguration($appRoot, $config)
    {
        self::$appRoot = $appRoot;
        self::$config = $config;
        $this->setEnvironment();
    }

    /**
     * Resets the configuration to the one specified by config.json
     */
    public function resetConfiguration()
    {
        self::$appRoot = dirname(dirname(dirname(__DIR__)));
        self::$config = json_decode(file_get_contents(self::$appRoot . '/config.json'), true);
        $this->setEnvironment();
    }

    /**
     * Returns the Bootstrap instance.
     *
     * @return Bootstrap
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            // @codeCoverageIgnoreStart
            self::$instance = new self();
        }
        // @codeCoverageIgnoreEnd
        return self::$instance;
    }

    /**
     * Returns the installation type, which is the name of the current environment, e.g. "development" or "production".
     *
     * @return string
     */
    public function getInstallationType()
    {
        return self::$environment;
    }

    /**
     * Returns allowed origins for CORS.
     *
     * @return string
     */
    public function getAllowedOrigins()
    {
        return empty(self::$info['origins']) ? '' : self::$info['origins'];
    }

    /**
     * Returns the URL of the corresponding API
     */
    public function getApiUrl()
    {
        return empty(self::$info['apiurl']) ? $this->getWebAppUrl() . '/api' : self::$info['apiurl'];
    }

    /**
     * Returns the URL of the corresponding web application
     */
    public function getWebAppUrl()
    {
        return empty(self::$info['appurl']) ? 'http://localhost' : self::$info['appurl'];
    }

    /**
     * Returns the application root directory path
     *
     * @return string
     */
    public function getAppRoot()
    {
        return self::$appRoot;
    }

    /**
     * Returns the site configuration.
     *
     * @return mixed
     */
    public function getConfiguration()
    {
        return self::$config;
    }

    /**
     * Sets a new site configuration
     */
    public function setConfiguration($config)
    {
        self::$config = $config;
        $encoded = json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        file_put_contents(self::$appRoot . '/config.json', $encoded);
    }

    /**
     * Updates the current installation environment on base of configuration parameters.
     *
     * @throws InvalidParameterException if configuration is not sufficient
     */
    private function setEnvironment()
    {
        if (empty(self::$config['environments'])) {
            throw new InvalidParameterException('config.json should contain information about environments');
        }
        foreach (self::$config['environments'] as $environment => $info) {
            if (empty($info['approot'])) {
                throw new InvalidParameterException(
                    "config.json environment '$environment' should contain at least 'approot'"
                );
            }
            if ($info['approot'] === self::$appRoot) {
                self::$environment = $environment;
                self::$info = $info;
                return;
            }
        }
        throw new InvalidParameterException("Environment for cwd='" . self::$appRoot . "' not found");
    }
}
