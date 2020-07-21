<?php
namespace enflares\System;

/**
 * Interface DataInterface
 * @package enflares\System
 */
interface DataInterface
{
    /**
     * Property getter
     * @param string|int $name
     * @return mixed
     */
    public function __get($name);

    /**
     * Property setter
     * @param string|int $name
     * @param $value
     * @return mixed
     */
    public function __set($name, $value);

    /**
     * Check if a property exists
     * @param string|int $name
     * @return mixed
     */
    public function __isset($name);

    /**
     * Remove a dynamic property or set the property to NULL
     * @param string|int $name
     * @return mixed
     */
    public function __unset($name);

    /**
     * Set property values as a lot
     * @param array|NULL $data
     * @return mixed
     */
    public function __assign(Array $data=NULL);

    /**
     * Return the properties as an array
     * @return array
     */
    public function __toArray();
}