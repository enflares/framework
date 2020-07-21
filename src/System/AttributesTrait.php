<?php
namespace enflares\System;

trait AttributesTrait
{
    /**
     * @var array
     */
    private $__ = [];

    public function attributes(Array $data=NULL)
    {
        if( func_num_args() ) $this->__ = array_change_key_case((array)$data, CASE_LOWER);

        return $this->__;
    }

	public function getAttribute($key)
    {
        $name = strtolower($key);
        if( isset($this->__[$name]) ) return $this->__[$name];
        
        $name = strtolower(preg_replace('/([A-Z])/', '-\\1', $key));
        return $this->__[$name] ?? NULL;
    }
    
	public function setAttribute($key, $value)
    {
        $name = strtolower(preg_replace('/([A-Z])/', '-\\1', $key));
        $this->__[$name] = $value;
        return $this;
    }
    
	public function hasAttribute($key)
    {
        return isset($this->__[strtolower($key)])
                || isset($this->__[strtolower(preg_replace('/([A-Z])/', '-\\1', $key))]);
    }
    
	public function removeAttribute($key)
    {
        unset($this->__[strtolower($key)]);
        unset($this->__[strtolower(preg_replace('/([A-Z])/', '-\\1', $key))]);

        return $this;
    }
    
	public function clearAttributes()
    {
        $this->__ = [];
        return $this;
    }
}

