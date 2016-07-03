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
 * Handles global configuration
 *
 * @package    justso
 */
class Bootstrap
{
    private $appRoot;

    private $config;

    private $environment;

    private $info;

    /**
     * Initializes the Bootstrap configuration.
     */
    public function __construct($appRoot = null, array $config = null)
    {
        $this->appRoot = $appRoot ?: dirname(dirname(dirname(__DIR__)));
        $this->config = $config ?: json_decode(file_get_contents($this->appRoot . '/config.json'), true);
        $this->setEnvironmentInfo();
    }

    /**
     * Sets the configuration in an existing Bootstrap config for testing purposes - don't use for other things.
     *
     * @param string $appRoot Simulated application root folder
     * @param array  $config  New test configuration
     */
    public function setTestConfiguration($appRoot = null, array $config = null)
    {
        if ($appRoot !== null) {
            $this->appRoot = $appRoot;
        }
        if ($config !== null) {
            $this->config = $config;
        }
        $this->setEnvironmentInfo();
    }

    /**
     * Resets the configuration to the one specified by config.json
     */
    public function resetConfiguration()
    {
        $this->appRoot = dirname(dirname(dirname(__DIR__)));
        $this->config = json_decode(file_get_contents($this->appRoot . '/config.json'), true);
        $this->setEnvironmentInfo();
    }

    /**
     * Returns the Bootstrap instance.
     *
     * @return Bootstrap
     * @deprecated Use new Bootstrap() instead.
     */
    public static function getInstance()
    {
        return new self();
    }

    /**
     * Returns the installation type, which is the name of the current environment, e.g. "development" or "production".
     *
     * @return string
     */
    public function getInstallationType()
    {
        return $this->environment;
    }

    /**
     * Returns allowed origins for CORS.
     *
     * @return string
     */
    public function getAllowedOrigins()
    {
        return empty($this->info['origins']) ? '' : $this->info['origins'];
    }

    /**
     * Returns the URL of the corresponding API
     */
    public function getApiUrl()
    {
        return empty($this->info['apiurl']) ? $this->getWebAppUrl() . '/api' : $this->info['apiurl'];
    }

    /**
     * Returns the URL of the corresponding web application
     */
    public function getWebAppUrl()
    {
        return empty($this->info['appurl']) ? 'http://localhost' : $this->info['appurl'];
    }

    /**
     * Returns the application root directory path
     *
     * @return string
     */
    public function getAppRoot()
    {
        return $this->appRoot;
    }

    /**
     * Returns the site configuration.
     *
     * @return mixed
     */
    public function getConfiguration()
    {
        return $this->config;
    }

    /**
     * Sets a new site configuration
     */
    public function setConfiguration($config)
    {
        $this->config = $config;
        $encoded = json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        file_put_contents($this->appRoot . '/config.json', $encoded);
    }

    private function setEnvironmentInfo()
    {
        if (empty($this->config['environments'])) {
            throw new InvalidParameterException('config.json should contain information about environments');
        }
        foreach ($this->config['environments'] as $environment => $info) {
            if (empty($info['approot'])) {
                throw new InvalidParameterException(
                    "config.json environment '$environment' should contain at least 'approot'"
                );
            }
            if ($info['approot'] === $this->appRoot) {
                $this->environment = $environment;
                $this->info = $info;
                return;
            }
        }
        throw new InvalidParameterException("Environment for cwd='" . $this->appRoot . "' not found");
    }
}
