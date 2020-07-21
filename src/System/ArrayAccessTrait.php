<?php
namespace enflares\System;

/**
 * Trait ArrayAccessTrait
 * @package enflares\System
 */
trait ArrayAccessTrait
{
    /**
     * @inheritDoc
     */
    public function offsetExists($offset)
    {
        return isset($this->$offset);
    }

    /**
     * @inheritDoc
     */
    public function offsetGet($offset)
    {
        return $this->$offset;
    }

    /**
     * @inheritDoc
     */
    public function offsetSet($offset, $value)
    {
        return $this->__set($offset, $value);
    }

    /**
     * @inheritDoc
     */
    public function offsetUnset($offset)
    {
        return $this->__unset($offset);
    }
}