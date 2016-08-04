<?php
/**
 * Definition of interface DependencyContainerInterface
 *
 * @copyright  2015-today Justso GmbH
 * @author     j.schirrmacher@justso.de
 * @package    justso\justapi
 */

namespace justso\justapi;

/**
 * Description
 */
interface DependencyContainerInterface
{
    public function newInstanceOf($name);
    public function get($name, array $arguments = null);
}
