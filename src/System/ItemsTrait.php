<?php
namespace enflares\System;

/**
 * Trait ItemsTrait
 * @package enflares\System
 */
trait ItemsTrait
{
    
    /**
     * Items
     *
     * @var array
     */
    protected $items = [];
    /**
     * Return all the items to the section
     *
     * @return array
     */
    public function items()
    {
        return $this->items;
    }

    /**
     * To check if an item exists
     *
     * @param int $offset
     * @return boolean
     */
    public function has($offset)
    {   
        return isset($this->items[$offset]) 
            || in_array($offset, $this->items, TRUE);
    }

    /**
     * Get the item value
     *
     * @param string $offset
     * @param mixed $default
     * @return string
     */
    public function get($offset, $default=NULL)
    {
        return isset($this->items[$offset]) 
            ? $this->items[$offset] 
            : $default;
    }

    /**
     * Set the item value
     *
     * @param string $offset
     * @param mixed $value
     * @return $this
     */
    public function set($offset, $value)
    {
        $this->items[$offset] = $value;

        return $this;
    }

    /**
     * Append the item value
     *
     * @param mixed $value
     * @param null $key
     * @return $this
     */
    public function append($value, $key=NULL)
    {
        if( $key ) $this->items[$key] = $value;
        else $this->items[] = $value;

        return $this;
    }

    /**
     * Prepend the item value
     *
     * @param mixed $value
     * @return $this
     */
    public function prepend($value)
    {
        array_unshift($this->items, $value);
        
        return $this;
    }

    /**
     * Remove an item off the section
     *
     * @param string $offset
     * @return $this
     */
    public function dispose($offset)
    {
        if( isset($this->items[$offset]) ) unset($this->items[$offset]);
        else unset($this->items[array_search($offset, $this->items, TRUE)]);

        return $this;
    }

    /**
     * Return the number of items in the collection
     *
     * @return int
     */
    public function count()
    {
        return count($this->items);
    }
}