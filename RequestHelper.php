<?php
/**
 * Definition of class RequestHelper
 *
 * @copyright  2013-today Justso GmbH
 * @author     j.schirrmacher@justso.de
 * @package    justso
 */

namespace justso\justapi;

/**
 * Helps with request parameter validation
 *
 * @package    justso
 */
class RequestHelper
{
    /**
     * @var array
     */
    private $params = array();

    /**
     * @var array
     */
    private $server = array();

    /**
     * Initializes the RequestHelper.
     */
    public function __construct()
    {
        $this->params = $_REQUEST + $_FILES;
        $this->server = $_SERVER;
    }

    /**
     * Returns the $_SERVER data
     *
     * @return mixed
     */
    public function getServerParams()
    {
        return $this->server;
    }

    /**
     * Replaces parameter data completely.
     *
     * @param array $data
     * @param array $server
     */
    public function set(array $data = array(), array $server = array())
    {
        $this->params = $data;
        $this->server = $server;
    }

    /**
     * Adds the specified data to the set of parameters.
     *
     * @param array $data
     * @param array $server
     */
    public function fillWithData(array $data, array $server = array())
    {
        $this->params += $data;
        $this->server += $server;
    }

    /**
     * Returns all parameters without any checks, so remember to check each parameter individually!
     *
     * @return array
     */
    public function getAllParams()
    {
        return $this->params;
    }

    /**
     * Returns a parameter value.
     *
     * @param string $name
     * @param string $default
     * @param bool $optional
     * @return string
     * @throws InvalidParameterException
     */
    public function getParam($name, $default = null, $optional = false)
    {
        if (isset($this->params[$name])) {
            return $this->params[$name];
        } elseif (!$optional) {
            throw new InvalidParameterException("Parameter '{$name}' is missing or invalid");
        } else {
            return $default;
        }
    }

    /**
     * Returns the value of the specified parameter as a string containing only alphanumeric characters which can be
     * used as an identifier name (A-Za-z0-9_).
     *
     * @param $name
     * @param null $default
     * @param bool $optional
     * @return string
     * @throws InvalidParameterException
     */
    public function getIdentifierParam($name, $default = null, $optional = false)
    {
        $value = $this->getParam($name, $default, $optional);
        if ($value !== null) {
            if (!preg_match('/^[A-Za-z][\w\-]*$/', $value)) {
                throw new InvalidParameterException("Parameter '{$name}' is invalid");
            }
        }
        return $value;
    }

    /**
     * Returns the value of the specified parameter as an integer value.
     *
     * @param string $name
     * @param int    $default
     * @param bool   $optional
     * @return int
     * @throws InvalidParameterException
     */
    public function getIntParam($name, $default = null, $optional = false)
    {
        $value = $this->getParam($name, $default, $optional);
        if ($value !== null) {
            if (!preg_match('/^\-?\d*$/', $value)) {
                throw new InvalidParameterException("Parameter '{$name}' is invalid");
            }
            $value = (int)$value;
        }
        return $value;
    }

    /**
     * Returns the value of the specified parameter as a boolean value.
     *
     * @param  string $name
     * @param  bool   $default
     * @param  bool   $optional
     * @return bool
     * @throws InvalidParameterException
     */
    public function getBooleanParam($name, $default = null, $optional = false)
    {
        $value = $this->getParam($name, $default, $optional);
        if ($value !== null) {
            if (!preg_match('/^(0|1|true|false)$/', $value)) {
                throw new InvalidParameterException("Parameter '{$name}' is invalid");
            }
            $value = $value === '1' || $value === 'true';
        }
        return $value;
    }

    /**
     * Returns the value of the specified parameter as an integer value.
     * The parameter has to be greater than 0, else an InvalidParameterException is thrown.
     *
     * @param string $name
     * @param null   $default
     * @param bool   $optional
     * @return int
     * @throws InvalidParameterException
     */
    public function getKeyParam($name, $default = null, $optional = false)
    {
        $value = $this->getIntParam($name, $default, $optional);
        if ($value !== null) {
            if ($value <= 0) {
                throw new InvalidParameterException("Parameter '{$name}' is invalid");
            }
        }
        return $value;
    }

    /**
     * Returns the value of the specified parameter if it is an e-mail address.
     *
     * @param string $name
     * @param string $default
     * @param bool   $optional
     * @return string
     * @throws InvalidParameterException
     */
    public function getEMailParam($name, $default = null, $optional = false)
    {
        $value = $this->getParam($name, $default, $optional);
        if ($value !== null) {
            if (!preg_match('/^.+@.+\...+$/', $value)) {
                throw new InvalidParameterException("Parameter '{$name}' is invalid");
            }
        }
        return $value;
    }

    /**
     * Checks if param value is set and not empty
     *
     * @param string $name
     * @return bool
     */
    public function isParamSet($name)
    {
        return !empty($this->params[$name]);
    }

    /**
     * Returns
     * @param string $name
     * @param bool   $optional
     * @return string
     * @throws InvalidParameterException
     */
    public function getFileParam($name, $optional = false)
    {
        $value = $this->getParam($name, null, $optional);
        if ($value !== null) {
            if (!is_array($value)) {
                throw new InvalidParameterException("Parameter '{$name}' is invalid");
            }
        }
        return $value;
    }
}
