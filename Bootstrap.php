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

    /**
     * Initializes the system
     */
    private function __construct()
    {
        self::$appRoot = dirname(dirname(dirname(__DIR__)));
        self::$config = json_decode(file_get_contents(self::$appRoot . '/config.json'), true);
        $packages = array_merge(array('justso'), self::$config['packages']);
        foreach ($packages as $package) {
            $autoloader = new Autoloader($package, self::$appRoot . '/vendor');
            $autoloader->register();
        }
    }

    /**
     * @param null $domain
     *
     * @return Bootstrap
     */
    public static function getInstance($domain = null)
    {
        if (self::$instance === null) {
            self::$instance = new self($domain);
        }
        return self::$instance;
    }

    /**
     * Returns the installation type (may be 'development', 'autotest', 'integration' or 'production'.
     *
     * @return string
     */
    public function getInstallationType()
    {
        if (preg_match('/^\/var\/lib\/jenkins\//', __FILE__)) {
            return 'autotest';
        } elseif (preg_match('/^\/var\/www\/test\//', __FILE__)) {
            return 'integration';
        } elseif (preg_match('/^\/var\/www\/prod\//', __FILE__)) {
            return 'production';
        } else {
            return 'development';
        }
    }

    /**
     * Returns allowed origins for CORS.
     *
     * @return string
     */
    public function getAllowedOrigins()
    {
        $allowedHosts = array(
            'development' => 'http://local.',
            'autotest'    => 'http://local.',
            'integration' => 'https://test.',
            'production'  => 'https://',
        );
        $type = $this->getInstallationType();
        if (isset($allowedHosts[$type])) {
            return $allowedHosts[$type] . self::$config['domain'];
        } else {
            return '';
        }
    }

    /**
     * Returns the URL of the corresponding API
     */
    public function getApiUrl()
    {
        $apiURLs = array(
            'development' => 'http://localapi.',
            'autotest'    => 'http://autotestapi.',
            'integration' => 'https://testapi.',
            'production'  => 'https://api.',
        );
        $type = $this->getInstallationType();
        if (isset($apiURLs[$type])) {
            return $apiURLs[$type] . self::$config['domain'] . '/';
        } else {
            return '';
        }
    }

    /**
     * Returns the URL of the corresponding web application
     */
    public function getWebAppUrl()
    {
        $webAppUrls = array(
            'development' => 'http://local.' . self::$config['domain'] . '/',
            'autotest'    => 'http://localhost',
            'integration' => 'https://test.' . self::$config['domain'] . '/',
            'production'  => 'https://' . self::$config['domain'] . '/',
        );
        $type = $this->getInstallationType();
        if (isset($webAppUrls[$type])) {
            return $webAppUrls[$type];
        } else {
            return '';
        }
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
        file_put_contents(self::$appRoot . '/config.json', json_encode($config, JSON_PRETTY_PRINT));
    }
}
