<?php
namespace enflares\System;

/**
 * Interface AttributeInterface
 * @package enflares\System
 */
interface AttributeInterface
{
    /**
     * Returns all attributes
     * @return mixed
     */
    public function attributes();

    /**
     * Gets one attribute
     * @return mixed
     */
	public function getAttribute();

    /**
     * Sets the value to an attribute
     * @param string $key
     * @param $value
     * @return mixed
     */
	public function setAttribute($key, $value);

    /**
     * Checks if the attribute exists
     * @param $key
     * @return mixed
     */
	public function hasAttribute($key);

    /**
     * Removes an attribute
     * @param $key
     * @return mixed
     */
	public function removeAttribute($key);

    /**
     * Remove all attributes
     * @return mixed
     */
	public function clearAttributes();
}

