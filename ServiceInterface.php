<?php
/**
 * Definition of ServiceInterface
 *
 * @copyright  2014-today Justso GmbH
 * @author     j.schirrmacher@justso.de
 * @package    justso
 */

namespace justso\justapi;

/**
 * Definition of the interface of services
 *
 * @package    justso
 */
interface ServiceInterface
{
    /**
     * Initializes the service with the request $environment.
     *
     * @param SystemEnvironmentInterface $environment
     */
    public function __construct(SystemEnvironmentInterface $environment);
}
