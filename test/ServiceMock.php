<?php
/**
 * definition of class ServiceMock
 *
 * @copyright  2014-today Justso GmbH
 * @author     j.schirrmacher@justso.de
 *
 * @package    justso\test
 */

namespace justso\justapi\test;

use justso\justapi\RestService;

/**
 * Mocks a rest service
 *
 * @package    justso\test
 */
class ServiceMock extends RestService
{
    public static $called = array(
        'setName'      => 0,
        'getAction'    => 0,
        'postAction'   => 0,
        'putAction'    => 0,
        'deleteAction' => 0,
    );

    public static $lastName = '';

    /**
     * @var string
     */
    public static $exception = null;

    public function setName($serviceName)
    {
        self::$called[__FUNCTION__]++;
        parent::setName($serviceName);
        self::$lastName = $serviceName;
    }

    public function getAction()
    {
        self::$called[__FUNCTION__]++;
        if (self::$exception !== null) {
            throw new self::$exception;
        }
        parent::getAction();
    }

    public function postAction()
    {
        self::$called[__FUNCTION__]++;
        parent::postAction();
    }

    public function putAction()
    {
        self::$called[__FUNCTION__]++;
        parent::putAction();
    }

    public function deleteAction()
    {
        self::$called[__FUNCTION__]++;
        parent::deleteAction();
    }
}
