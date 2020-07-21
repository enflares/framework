<?php
namespace enflares\System;

/**
 * Class Exception
 * @package enflares\System
 */
class Exception extends \Exception
{
    const ERROR_CODE = E_USER_ERROR;

    /**
     * @param null $message
     * @param null $args
     * @throws Exception
     */
    public static function trigger($message=NULL, $args=NULL)
    {
        if( $message ) $message = _t(...func_get_args());
        throw $ex = new static($message, static::ERROR_CODE);
    }
}