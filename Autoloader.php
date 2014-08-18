<?php
/**
 * Definition of PSR-0 compliant autoloader class
 *
 * @copyright 2013-today Justso GmbH
 * @author    j.schirrmacher@justso.de
 * @package   justso
 */

namespace justso\justapi;

/**
 * Autoloader implementation that implements the technical interoperability
 * standards for PHP 5.3 namespaces and class names.
 *
 * Taken from https://gist.github.com/221634
 *
 * http://groups.google.com/group/php-standards/web/final-proposal
 *
 *     // Example which loads classes for the Doctrine Common package in the
 *     // Doctrine\Common namespace.
 *     $classLoader = new Autoloader('Doctrine\Common', '/path/to/doctrine');
 *     $classLoader->register();
 *
 * @author Jonathan H. Wage <jonwage@gmail.com>
 * @author Roman S. Borschel <roman@code-factory.org>
 * @author Matthew Weier O'Phinney <matthew@zend.com>
 * @author Kris Wallsmith <kris.wallsmith@gmail.com>
 * @author Fabien Potencier <fabien.potencier@symfony-project.org>
 */
class Autoloader
{
    private $fileExtension = '.php';
    private $namespace;
    private $includePath;
    private $namespaceSeparator = '\\';

    /**
     * Creates a new <tt>Autoloader</tt> that loads classes of the
     * specified namespace.
     *
     * @param string $ns          The namespace to use.
     * @param string $includePath Path to classes
     * @codeCoverageIgnore
     */
    public function __construct($ns = null, $includePath = null)
    {
        $this->namespace = $ns;
        $this->includePath = $includePath;
    }

    /**
     * Sets the namespace separator used by classes in the namespace of this class loader.
     *
     * @param string $sep The separator to use.
     * @codeCoverageIgnore
     */
    public function setNamespaceSeparator($sep)
    {
        $this->namespaceSeparator = $sep;
    }

    /**
     * Gets the namespace separator used by classes in the namespace of this class loader.
     *
     * @return string
     * @codeCoverageIgnore
     */
    public function getNamespaceSeparator()
    {
        return $this->namespaceSeparator;
    }

    /**
     * Sets the base include path for all class files in the namespace of this class loader.
     *
     * @param string $includePath
     * @codeCoverageIgnore
     */
    public function setIncludePath($includePath)
    {
        $this->includePath = $includePath;
    }

    /**
     * Gets the base include path for all class files in the namespace of this class loader.
     *
     * @return string $includePath
     * @codeCoverageIgnore
     */
    public function getIncludePath()
    {
        return $this->includePath;
    }

    /**
     * Sets the file extension of class files in the namespace of this class loader.
     *
     * @param string $fileExtension
     * @codeCoverageIgnore
     */
    public function setFileExtension($fileExtension)
    {
        $this->fileExtension = $fileExtension;
    }

    /**
     * Gets the file extension of class files in the namespace of this class loader.
     *
     * @return string $fileExtension
     * @codeCoverageIgnore
     */
    public function getFileExtension()
    {
        return $this->fileExtension;
    }

    /**
     * Installs this class loader on the SPL autoload stack.
     * @codeCoverageIgnore
     */
    public function register()
    {
        spl_autoload_register(array($this, 'loadClass'));
    }

    /**
     * Uninstalls this class loader from the SPL autoloader stack.
     * @codeCoverageIgnore
     */
    public function unregister()
    {
        spl_autoload_unregister(array($this, 'loadClass'));
    }

    /**
     * Loads the given class or interface.
     *
     * @param  string $className The name of the class to load.
     * @return bool
     */
    public function loadClass($className)
    {
        $found = false;
        $nsSep = $this->namespace.$this->namespaceSeparator;
        if (null === $this->namespace || $nsSep === substr($className, 0, strlen($nsSep))) {
            $fileName = '';
            if (false !== ($lastNsPos = strripos($className, $this->namespaceSeparator))) {
                $namespace = substr($className, 0, $lastNsPos);
                $className = substr($className, $lastNsPos + 1);
                $fileName  = str_replace($this->namespaceSeparator, DIRECTORY_SEPARATOR, $namespace);
                $fileName .= DIRECTORY_SEPARATOR;
            }
            $fileName .= str_replace('_', DIRECTORY_SEPARATOR, $className) . $this->fileExtension;
            $filePath  = stream_resolve_include_path($this->includePath . DIRECTORY_SEPARATOR . $fileName);
            if ($filePath) {
                require $filePath;
                $found = true;
            }
        }
        return $found;
    }
}
