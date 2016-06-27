<?php
/**
 * definition of front controller for the backend API
 *
 * @copyright  2014-today Justso GmbH
 * @author     j.schirrmacher@justso.de
 * @package    justso
 */

namespace justso\justapi;

// @codeCoverageIgnoreStart
require_once('SystemEnvironment.php');

$env = new SystemEnvironment();
$factory = new RestServiceFactory($env);
$factory->handleRequest();
// @codeCoverageIgnoreEnd
