<?php
namespace enflares\System;

use ArrayIterator;

/**
 * Trait PropertyTrait
 * @package enflares\System
 */
trait PropertyTrait
{
    /**
     * @var array
     */
    private $_ = [];

    /**
     * PropertyTrait constructor.
     * @param array|NULL $data
     */
    public function __construct(Array $data=NULL)
    {
        $this->__assign($data);
    }

    /**
     * Get the object properties as an Array
     * @return array
     */
    public function __toArray()
    {
        return $this->_;
    }

    /**
     * Empty all dynamic properties
     * @return $this
     */
    public function __reset()
    {
        $this->_ = [];
        return $this;
    }

    /**
     * Set the property values as a lot
     * @param array|NULL $data
     * @return $this
     */
    public function __assign(Array $data=NULL)
    {
        foreach( (array)$data as $key=>$value )
            $this->$key = $value;
        return $this;
    }

    /**
     * Property getter
     * @param $key
     * @return mixed
     */
    public function __get($key)
    {
        if( method_exists($this, $func = 'get'.$key) )
            return $this->$func();

        if( property_exists($this, $key) )
            return $this->$key;

        if( isset($this->_[$key]) )
            return $this->_[$key];
    }

    /**
     * Property setter
     * @param $key
     * @param $value
     * @return mixed
     */
    public function __set($key, $value)
    {
        if( method_exists($this, $func = 'set'.$key) )
            return $this->$func($value);

        if( property_exists($this, $key) )
            return $this->$key = $value;

        return $this->_[$key] = $value;
    }

    /**
     * To check if a property exists
     * @param $key
     * @return bool
     */
    public function __isset($key)
    {
        return property_exists($this, $key) || isset($this->_[$key]);
    }

    /**
     * Remove a dynamic property or set the property to NULL
     * @param $key
     */
    public function __unset($key)
    {
        $this->__set($key, NULL);
        unset($this->_[$key]);
    }

    /**
     * @inheritDoc
     * @return ArrayIterator
     */
    public function getIterator()
    {
        return new ArrayIterator( $this->_ );
    }
}