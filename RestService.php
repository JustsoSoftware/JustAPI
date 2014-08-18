<?php
/**
 * Definition of RestService abstract base class
 *
 * @copyright  2013-today Justso GmbH
 * @author     j.schirrmacher@justso.de
 * @package    justso\service
 */

namespace justso\justapi;

/**
 * This is a base class for all REST services
 *
 * @package justso\service
 */
abstract class RestService implements ServiceInterface
{
    /**
     * @var SystemEnvironmentInterface
     */
    protected $environment;

    /**
     * Name of service
     * @var string
     */
    protected $name = null;

    /**
     * @param SystemEnvironmentInterface $environment
     */
    public function __construct(SystemEnvironmentInterface $environment)
    {
        $this->environment = $environment;
    }

    /**
     * Sets the name of this service.
     *
     * @param string $serviceName
     */
    public function setName($serviceName)
    {
        $this->name = $serviceName;
    }

    /**
     * Executes the requested get action.
     *
     * @throws \Exception
     * @return void
     */
    public function getAction()
    {
        throw new \Exception("GET method not implemented for this URL");
    }

    /**
     * Executes the requested post action.
     *
     * @throws \Exception
     * @return void
     */
    public function postAction()
    {
        throw new \Exception("POST method not implemented for this URL");
    }

    /**
     * Executes the requested put action.
     *
     * @throws \Exception
     * @return void
     */
    public function putAction()
    {
        throw new \Exception("PUT method not implemented for this URL");
    }

    /**
     * Executes the requested DELETE action.
     *
     * @throws \Exception
     * @return void
     */
    public function deleteAction()
    {
        throw new \Exception("DELETE method not implemented for this URL");
    }
}
