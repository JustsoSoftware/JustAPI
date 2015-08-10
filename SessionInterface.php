<?php
/**
 * Definition of interface SessionInterface
 * 
 * @copyright  2015-today Justso GmbH
 * @author     j.schirrmacher@justso.de
 * @package    justso\justapi
 */

namespace justso\justapi;

/**
 * Interface for classes handling session data
 */
interface SessionInterface
{
    public function getValue($name);
    public function setValue($name, $value);
    public function getId();
    public function isValueSet($name);
    public function activate();
 }
