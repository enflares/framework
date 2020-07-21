<?php
namespace enflares\System;

use BadMethodCallException;
use enflares\Db\Db;

/**
 * Class Module
 * @package enflares\System
 */
class Module extends Component
{
    /**
     * @var array
     */
    protected static $privileges = [];

    /**
     * Checks permission to operate
     * @param $resource
     * @param $permits
     * @param array|NULL $preserve
     * @throws Exception
     */
    protected static function checkPermit($resource, $permits, Array $preserve=NULL)
    {
        if( isset(static::$privileges[$resource = strtolower($resource)]) ) {
            foreach( (array)$permits as $permit ) {
                if( isset(static::$privileges[$resource][$permit])
                    && !static::$privileges[$resource][$permit] )
                    return DeniedException::trigger('Operation is denied');
            }
        }
    }

    /**
     * @return Db
     */
    public static function db()
    {
        return Db::getInstance();
    }

    /**
     * @param $name
     * @param $arguments
     * @return mixed
     */
    public static function __callStatic($name, $arguments)
    {
        if( method_exists($db = static::db(), $name) )
            return $db->$name(...$arguments);

        throw new BadMethodCallException('Call to undefined method '.static::class.'::'.$name.'()');
    }
}